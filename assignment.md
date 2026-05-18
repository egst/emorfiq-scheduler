# Komentář k zadání

> **Ze zadání:**
>
> - "Aktuálně použijeme Laravel, ale nechceme se na něho vázat a mít případně
>   otevřenou cestu k přepnutí na crunz."
> - "problém, kdy Laravel Mutex implementace je nespolehlivá a chceme
>   implementovat vlastní mutex".
> - "aby implementace Laravel tříd byla kdykoli vyměnitelná a nevázala se na
>   Laravel."

Na základě požadavků v zadání jsem si dal následjící cíle:

- Interface scheduleru:
    - Navrhnout společné rozhraní pro scheduler, které bude možné implementovat pro obě knihovny
    - Minimalizovat potřebný setup boilerplate pro obě knihovny
    - Umožnit poskytnout scheduleru jeho konfiguraci a definici jobů sjednoceným rozhraním nezávislým na knihovně
- Možnosti a limitace mutexu:
    - Zjistit, jaké nedostatky má Laravel oproti Crunzu 
    - Zjistit, jestli má nedostatky i Crunz
    - Rozmyslet si, v jakých případech relevantních pro scheduler mohou zámky selhat
    - Prozkoumat i jiné knihovny a zjistit, jestli to nějakým způsobem řeší
    - Určit, co je možné zaručit v rámci scheduleru a co si musí pohlídat job sám
- Interface mutexu:
    - Navrhnout architekturu kolem mutexu, ketrá umožní implementaci různých backendů nezávisle na knihovně
        - Přitom pokrýt očekávání obou knihoven
    - Ideálně by mělo být možné využít stejnou implementaci pro obě knihovny
        - Přitom nemíchat různou sémantiku zámků do stejného rozhraní

## Interface scheduleru

Pro jednoduchý základ nám postačí rozhraní `Scheduler` s metodou `addJob`. Pro
obecnou konfiguraci scheduleru navíc třída `SchedulerConfig` a pro definici
jobů třída `Job`.

Takto máme **jednoduše a přehledně modelovanou doménu scheduleru** a velmi
**flexibilní rozhraní** pro jednoduchý výčet jobů nebo pro integraci do nějakého
komplexnějšího systému v projektu např. pomocí atributů/interfaců a class
discovery tříd, které poskytnou definice jobů. To už je ale záležitost
aplikační vrstvy.

Mohli bychom **do budoucna přidat nějaký job builder s fluent interface** typickým
pro podobné scheduler knihovny.

Implementace rozhraní `Scheduler` pak provádí konkrétní operace nad objekty knihoven.

**Inicializace schedulerů je specifická pro každou knihovnu.** Je tedy potřeba
nějaký **minimální setup boilerplate** na různých místech podle zvolené knihovny.
Všechny implementace ale umožňují využití společného `SchedulerConfig`
pro **konfiguraci nezávisle na knihovně**.

Adresář `examples/` obsahuje ukázky využití pro obě knihovny.

## Možnosti a limitace mutexu

Během prozkoumávání dokumentace a implementačních detailů obou knihoven jsem
narazil na několik důležitých rozdílů, které komplikují sjednocení rozhraní
mutexu. Nerad bych to zbytečně vypisoval do detailů, nechám to na osobní
konzultaci. Zde vypíšu jen shrnutí:

- ⚠️ Crunz využívá Symfony Lock zámky s vlastnictvím.
- ⚠️ Laravel očekává zámky bez vlastnictví.
    - To je také jeho největší nevýhoda, protože dlouho běžící job může odemknout zámek jiného procesu.
    - Nelze to řešit bez závažných tradeoffů.
        - Nefunkčnost utilit schedule list a cache clear.
        - Nefunkčnost uvolnění zámků jobů běžících na pozadí.
- Laravel navíc umí tzv. "minute lock" (aka "on one server")
    - V Crunzu by to šlo nasimulovat vlastním zámkem a `skip` callbackem.
- Crunz navíc obnovuje živostnost dlouho běžících jobů.
    - Dělá to nedeterministicky - spíše best-effort pokus.
- Oba mechanismy tedy nejsou naprosto "spolehlivé".
    - Kritické joby by si měly samy zaručit zamezení konfliktů.
    - Dlouho bežící joby občas poběží bez zámku.
- Crunz spouští joby jen paralelně.
    - Běží jeden hlavní proces, který drží zámky a čeká na ukončení subprocesů.
    - Zvládne tak token-based i session-based lock ownerhsip.
- Laravel spouští joby sekvenčně by default.
    - Pomocí jobu v "commandu" ho lze pustit i paralelně.
    - Zámek se pak odemyká v jiném procesu.
    - To komplikuje možnost využít session-based lock ownership.
- 💡 Možné a docela elegantní řešení rozdílů zamykání:
    - Nepoužívat zamykání knihoven vůbec.
    - Spravovat zamykání uvnitř closure jobu (tedy potenciálně i v jiném procesu).
    - Můžeme tak využít token-based i session-based lock ownership.
    - Bonus: Můžeme předat lock handle jobu, který si pohlídá TTL.
- 💡 Stojí za zvážení i alternativní knihovna: [orisai/scheduler](https://github.com/orisai/scheduler)
    - Zamykání uvnitř subprocesu jobu.
    - Paralelní i sekvenční exekuce s možností využít session-based i token-based vlastnictví zámku.
    - Předání lock handlu do handleru jobu.
    - Overlap lock i minute lock.
    - Možnost využít Symfony Lock.

## Interface mutexu

V návaznosti na moje úvahy a zjištění o implementačních detailech obou knihoven
jsem se rozhodl nemíchat dohromady dvě různé sémantiky zámků.

Pro implementaci běžných backendů jsem tak navrhnul **jednoduchá procedurální
rozhraní**:
- `SharedStore`:  Ukládání a správa zámků bez vlastnictví
- `TokenStore`:   Vlastnictví zámků na základě volajícím drženého tokenu.
    - Funguje napříč procesy, pokud se předá token.
- `SessionStore`: Vlastnictví zámků na základě otevřeného připojení na backend.
    - Nevyžaduje ukládat token, ale funguje jen v rámci jednoho procesu.

✅ Pro Laravel implementaci scheduleru stačí uvést konkrétní implementaci
`SharedStore`. Scheduler pak využije adaptér na `EventMutex` (Laravel).

💡 Pro Crunz by stačilo uvést `TokenStore|SessionStore`. Scheduler by pak využil
jednoduchý adaptér na `PersistingStoreInterface` (Symfony).

**Nevýhoda:** Nemůžeme používat existující Symfony backendy.

💡 Mohli bychom rozšířit společné rozhraní o `PersistingStoreInterface`.

**Nevýhoda:** Pevná závislost na Symfony.

💡 A nebo navrhnout vlastní mezivrstvu, která umožní implementovat
`PersistingStoreInterface` z našich "store" implementací i z samotného
`PersistingStoreInterface` bez pevné závislosti v rozhraní.

**Nevýhoda:** Možná zbytečná komplexita, pokud to v praxi nepotřebujeme.

Rozhodl jsem se pro tu variantu s **vlastní mezivrstvou, která modeluje zamykání
s handle objektem**, který určuje vlastnictví zámku. Uznávám ale, že to může být
trochu overkill, takže to beru spíše jako takový proof-of-concept, že
teoreticky můžeme mít **obecné rozhraní, které lze implementovat z libovolného
third-party backendu**, který využívá nějaký "opaque" handle objekt určující
vlastnictví zámků. V praxi bych se pak omezil jen na rozhraní "store", pokud
Symfony nebo jiné third-party backendy neplánujeme využívat.

Řešením je tedy OOP rozhraní `LockProvider` + `LockHandle`. Takový přístup
přidává komplexitu, ale má následující výhody:
- Je velmi jednoduše rozšířitelné.
- Lock handle jasně modeluje a hlídá vlastnictví zámku.
- Lock handle umožňuje držení third-party lock handle objektů (napr. Symfony
  `Key`) aniž bychom museli sahat na jeho interní implementation-defined stav.
- Díky metodám `LockHandle::snapshot` a `LockProvider::recreate` můžeme i
  naopak ukládat jen potřebná ownership data do third-party lock handle objektů
  a dokonce zachovat serializovatelnost, pokud to dává smysl pro daný backend.

Implementuje se pomocí adaptérů nad `TokenStore` a `SessionStore` nebo přímo z
nějakého third-party rozhraní.

Ve společné konfiguraci scheduleru jsem se rozhodl jít tou
bezpečnější/stabilnější cestou a nechat uživatele uvést dvě různé mutex
implementace. `SharedStore` pro "shared locking" a `LockProvider` pro "owned
locking". Laravel scheduler potom využije tu "shared" variantu, Crunz využije
tu "owned" variantu.

Pokud neimplementujeme Laravel adaptér nad shared locking backendem (což je
problematické), musímě mít obě varianty zvlášť.

Samotné implementace vlastních mutexů ale můžou být sjednocené, jen je třeba
explicitně uvést a implementovat správnou sémantiku zámků. Např. `PdoStore`
implementuje `SharedStore` i `TokenStore`, ale `PostgreAdvisoryStore` zvládne
jen `SessionStore`, protože PostgreSQL advisory zámky nemohou být sdíleny mezi
procesy.
