<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Dutch (nl-BE) SEO pass over the existing public pages: unique titles and
 * meta descriptions, a clear primary heading, Dutch copy for sections that
 * still held English source text, and the missing nl UI translations.
 *
 * Content lives in the database (Admin → Pages), so it ships as a data
 * migration — `php artisan migrate` on deploy applies it to the live
 * database. Only existing pages are touched (matched by slug); no slug,
 * route or link destination changes. Everything stays editable in the admin
 * panel afterwards.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->updatePages();
        $this->updateSections();
        $this->updateAirportAreas();
        $this->updateSettings();
        $this->upsertDutchTranslations();

        // NAP consistency: the home page's admin-authored LocalBusiness
        // schema spelled the name "QasimTaxi" while everything else (and
        // the visible site) says "Qasim Taxi".
        DB::table('pages')->where('slug', 'home')->where('custom_schema', 'like', '%"name": "QasimTaxi"%')->update([
            'custom_schema' => DB::raw("REPLACE(custom_schema, '\"name\": \"QasimTaxi\"', '\"name\": \"Qasim Taxi\"')"),
        ]);

        DB::table('blog_categories')->where('slug', 'travel-tips')->update([
            'name' => 'Reistips',
            'updated_at' => now(),
        ]);

        Cache::flush();
    }

    public function down(): void
    {
        // Content-only migration: the previous copy is not restored
        // automatically (take a database backup before deploying).
    }

    private function updatePages(): void
    {
        $pages = [
            'about' => [
                'meta_title' => 'Over Qasim Taxi | Lokale taxidienst uit Bilzen',
                'meta_description' => 'Qasim Taxi is een lokale taxidienst uit Bilzen. Lees wie we zijn, hoe we werken en hoe je eenvoudig een taxi reserveert in Bilzen en omgeving.',
            ],
            'services' => [
                'meta_title' => 'Onze taxidiensten in Bilzen | Qasim Taxi',
                'meta_description' => 'Luchthavenvervoer, lokale taxiritten, zakelijk vervoer, ritten per uur en lange ritten vanuit Bilzen. Bekijk alle diensten van Qasim Taxi en reserveer online.',
            ],
            'faq' => [
                'meta_title' => 'Veelgestelde vragen over taxi reserveren in Bilzen | Qasim Taxi',
                'meta_description' => 'Antwoorden op vragen over een taxi reserveren in Bilzen: prijzen, bagage, betalen, luchthavenvervoer en annuleren bij Qasim Taxi.',
            ],
            'areas' => [
                'name' => 'Werkgebied',
                'meta_title' => 'Werkgebied: taxi in Bilzen en omliggende gemeenten | Qasim Taxi',
                'meta_description' => 'Qasim Taxi rijdt vanuit Bilzen naar onder meer Hasselt, Genk, Tongeren, Maastricht en verschillende luchthavens. Bekijk ons werkgebied en reserveer je taxi.',
            ],
            'privacy-policy' => [
                'meta_title' => 'Privacybeleid | Qasim Taxi',
                'meta_description' => 'Lees hoe Qasim Taxi uit Bilzen omgaat met je persoonsgegevens wanneer je een taxi reserveert of contact met ons opneemt.',
            ],
            'terms' => [
                'meta_title' => 'Algemene voorwaarden | Qasim Taxi',
                'meta_description' => 'De algemene voorwaarden van Qasim Taxi voor het reserveren en gebruiken van onze taxidienst in Bilzen en omgeving.',
            ],
            'refund-policy' => [
                'meta_title' => 'Terugbetalingsbeleid | Qasim Taxi',
                'meta_description' => 'Lees wanneer je recht hebt op een terugbetaling als je een taxirit bij Qasim Taxi annuleert en hoe we terugbetalingen verwerken.',
            ],
            'cookie-policy' => [
                'meta_title' => 'Cookiebeleid | Qasim Taxi',
                'meta_description' => 'Ontdek welke cookies de website van Qasim Taxi gebruikt, waarvoor ze dienen en hoe je ze zelf beheert in je browser.',
            ],

            // Service pages were optimised in the previous pass; these only
            // replace breadcrumb/schema names that had been filled with a
            // full SEO title, and split /city-rides away from
            // /chauffeur-service, which carried the same H1 and a near
            // identical title ("Taxi Bilzen | Lokale Taxi ...").
            'airport-transfer' => ['name' => 'Luchthavenvervoer vanuit Bilzen'],
            'chauffeur-service' => ['name' => 'Lokale taxi in Bilzen'],
            'corporate-transfer' => ['name' => 'Zakelijk vervoer in Bilzen'],
            'city-rides' => [
                'name' => 'Taxiritten in Bilzen en omgeving',
                'meta_title' => 'Taxiritten naar Hasselt, Genk, Tongeren en Maastricht | Qasim Taxi',
                'meta_description' => 'Korte taxirit vanuit Bilzen naar Hasselt, Genk, Tongeren, Maastricht of een gemeente in de buurt? Bekijk de vaste routeprijzen en reserveer je rit online.',
            ],
            'hourly-rides' => ['name' => 'Taxi per uur in Bilzen'],
            'vip-transport' => ['name' => 'Lange taxiritten vanuit Bilzen'],
        ];

        foreach ($pages as $slug => $data) {
            DB::table('pages')->where('slug', $slug)->update($data + ['updated_at' => now()]);
        }
    }

    private function updateSections(): void
    {
        // About
        $this->section('about', 'rich_text', [
            'heading' => 'Over Qasim Taxi: je lokale taxidienst in Bilzen',
            'body' => '<p>Qasim Taxi is een lokale taxidienst uit Bilzen, gevestigd in de Kloosterstraat 26. We brengen inwoners en bezoekers van Bilzen en de omliggende gemeenten van deur tot deur: van een korte rit door de stad tot een vroege rit naar de luchthaven.</p>'
                .'<p>Een taxi nemen hoort eenvoudig en voorspelbaar te zijn. Daarom zie je bij het online reserveren meteen een prijsschatting, rijden populaire routes aan een vaste prijs en stap je in een comfortabele, goed onderhouden Toyota C-HR met plaats voor maximaal 4 passagiers en 2 stuks bagage.</p>'
                .'<h3>Zo reserveer je een taxi bij Qasim Taxi</h3>'
                .'<ul><li>Online via het <a href="/#booking-widget">boekingsformulier</a>: vul je ophaaladres, bestemming, datum en uur in en je ziet meteen een prijsschatting.</li>'
                .'<li>Telefonisch of via WhatsApp op <a href="tel:+32467614860">+32 467 61 48 60</a>.</li>'
                .'<li>Met een bericht via onze <a href="/contact">contactpagina</a>.</li></ul>'
                .'<p>Benieuwd welke ritten we rijden? Bekijk <a href="/services">al onze taxidiensten</a> of ons <a href="/areas">werkgebied rond Bilzen</a>.</p>',
        ]);
        $this->section('about', 'vision_mission', [
            'content' => json_encode([
                'vision_icon' => 'eye',
                'vision_title' => 'Onze visie',
                'vision_body' => 'De taxidienst zijn waar mensen in Bilzen op rekenen: stipt, eerlijk geprijsd en met oprechte aandacht voor elke passagier.',
                'mission_icon' => 'trending-up',
                'mission_title' => 'Onze missie',
                'mission_body' => 'Veilig, comfortabel en betrouwbaar vervoer bieden aan iedereen in Bilzen en omgeving, met duidelijke prijzen en zonder verrassingen achteraf.',
            ], JSON_UNESCAPED_UNICODE),
        ]);
        $this->section('about', 'items', [
            'heading' => 'Onze waarden',
            'content' => json_encode([
                ['icon' => 'shield', 'title' => 'Betrouwbaar', 'description' => 'We plannen elke rit zo dat je op het afgesproken uur wordt opgehaald.'],
                ['icon' => 'users', 'title' => 'Klant op de eerste plaats', 'description' => 'Elke passagier en elke rit behandelen we met zorg en respect.'],
                ['icon' => 'globe', 'title' => 'Lokale kennis', 'description' => 'We kennen Bilzen en de omliggende gemeenten door en door.'],
            ], JSON_UNESCAPED_UNICODE),
        ]);

        // Services
        $this->section('services', 'items', [
            'heading' => 'Taxidiensten van Qasim Taxi in Bilzen',
            'subheading' => 'Van een korte rit in Bilzen tot een transfer naar de luchthaven: kies de dienst die bij je rit past.',
            'content' => json_encode([
                ['icon' => 'plane', 'title' => 'Luchthavenvervoer', 'description' => 'Ophalen en afzetten aan de luchthavens van Brussel, Antwerpen, Charleroi, Eindhoven, Maastricht Aachen en Düsseldorf.', 'link' => '/airport-transfer'],
                ['icon' => 'user', 'title' => 'Lokale taxi', 'description' => 'Comfortabele taxiritten in Bilzen en de omliggende gemeenten, van deur tot deur.', 'link' => '/chauffeur-service'],
                ['icon' => 'calendar', 'title' => 'Zakelijk vervoer', 'description' => 'Stipt en verzorgd vervoer naar vergaderingen, afspraken en zakenreizen.', 'link' => '/corporate-transfer'],
                ['icon' => 'car', 'title' => 'Taxiritten in de regio', 'description' => 'Ritten vanuit Bilzen naar Hasselt, Genk, Tongeren, Maastricht en andere plaatsen in de buurt.', 'link' => '/city-rides'],
                ['icon' => 'clock', 'title' => 'Taxi per uur', 'description' => 'Meerdere stops of een afspraak met wachttijd? Reserveer de taxi per uur.', 'link' => '/hourly-rides'],
                ['icon' => 'sparkles', 'title' => 'Lange ritten', 'description' => 'Lange afstanden binnen België en naar Nederland en Duitsland, met een lager kilometertarief naarmate de rit langer wordt.', 'link' => '/vip-transport'],
            ], JSON_UNESCAPED_UNICODE),
        ]);
        $this->addSectionIfMissing('services', 'rich_text', [
            'heading' => 'Welke taxidienst past bij jouw rit?',
            'body' => '<p>Voor een gewone rit in Bilzen of naar een gemeente in de buurt kies je een <a href="/chauffeur-service">lokale taxi</a> of een <a href="/city-rides">taxirit in de regio</a>. Moet je een vliegtuig halen, dan is <a href="/airport-transfer">luchthavenvervoer</a> de juiste keuze. Vertrek je naar Maastricht Aachen Airport of Brussels Airport, lees dan ook onze praktische info over een <a href="/taxi-maastricht-brussels-airport">taxi van Bilzen naar Maastricht Aachen Airport of Brussels Airport</a>.</p>'
                .'<p>Twijfel je? Vul je rit in via het <a href="/#booking-widget">boekingsformulier</a> voor een prijsschatting, of bel ons op <a href="tel:+32467614860">+32 467 61 48 60</a>. Antwoorden op de meest gestelde vragen vind je op onze pagina <a href="/faq">veelgestelde vragen</a>.</p>',
        ], 1);

        // FAQ — the only FAQ section on the dedicated FAQ page.
        $this->section('faq', 'faq', [
            'heading' => 'Veelgestelde vragen over taxi reserveren in Bilzen',
            'subheading' => 'Alles wat je wil weten voor je instapt.',
            'content' => json_encode([
                ['category' => 'Reserveren', 'question' => 'Hoe kan ik een taxi in Bilzen reserveren?', 'answer' => 'Je reserveert online via het boekingsformulier op onze homepagina: vul je ophaaladres, bestemming, datum en uur in en je ziet meteen een prijsschatting. Je kunt ook bellen of een WhatsApp-bericht sturen naar +32 467 61 48 60, of een bericht achterlaten via de contactpagina.'],
                ['category' => 'Reserveren', 'question' => 'Hoe ver op voorhand moet ik reserveren?', 'answer' => 'Reserveer zo vroeg als je kunt, zeker voor luchthavenritten en ritten vroeg in de ochtend. We proberen ook last-minute aanvragen zoveel mogelijk in te plannen.'],
                ['category' => 'Reserveren', 'question' => 'Wanneer is mijn rit bevestigd?', 'answer' => 'Na je online aanvraag krijg je een referentienummer. Je rit is definitief zodra Qasim Taxi je reservering bevestigt. Twijfel je, neem dan gerust contact op met je referentienummer bij de hand.'],
                ['category' => 'Reserveren', 'question' => 'Hoeveel passagiers en bagage kunnen mee?', 'answer' => 'Onze Toyota C-HR biedt plaats aan maximaal 4 passagiers en 2 stuks bagage. Reis je met meer bagage, vermeld dat dan bij je reservering zodat we samen kunnen bekijken wat mogelijk is.'],
                ['category' => 'Prijzen', 'question' => 'Hoeveel kost een taxirit?', 'answer' => 'De prijs hangt af van de afstand van je rit. In het boekingsformulier zie je meteen een prijsschatting voor je eigen ophaaladres en bestemming. Populaire routes, zoals ritten naar Hasselt, Genk, Maastricht en verschillende luchthavens, hebben een vaste prijs; die vind je bij Populaire routes op de homepagina.'],
                ['category' => 'Prijzen', 'question' => 'Hoe vraag ik een prijs of offerte aan?', 'answer' => 'Vul je rit in via het boekingsformulier voor een directe prijsschatting. Voor een rit op maat, een terugrit of meerdere stops kun je ons ook bellen, een WhatsApp-bericht sturen of het contactformulier gebruiken.'],
                ['category' => 'Luchthavenvervoer', 'question' => 'Wat als mijn vlucht vertraging heeft?', 'answer' => 'Geef je vluchtnummer door bij het reserveren. Dan passen we je ophaaltijd zo goed mogelijk aan.'],
                ['category' => 'Betalen', 'question' => 'Hoe kan ik betalen?', 'answer' => 'Je kunt contant of met je kaart betalen. Laat je voorkeur gewoon weten bij het reserveren.'],
                ['category' => 'Betalen', 'question' => 'Is fooi inbegrepen in de prijs?', 'answer' => 'Nee, fooi is niet inbegrepen. Je mag uiteraard een fooi geven als je dat wil.'],
                ['category' => 'Annuleren', 'question' => 'Hoe annuleer of wijzig ik mijn rit?', 'answer' => 'Neem zo snel mogelijk contact met ons op als je je rit wil annuleren of wijzigen, dan zoeken we samen een oplossing. Meer informatie vind je in ons terugbetalingsbeleid.'],
                ['category' => 'Algemeen', 'question' => 'Waar is Qasim Taxi gevestigd?', 'answer' => 'Qasim Taxi is gevestigd in de Kloosterstraat 26 in 3740 Bilzen. Van daaruit rijden we in Bilzen, de omliggende gemeenten en naar verschillende luchthavens.'],
            ], JSON_UNESCAPED_UNICODE),
        ]);

        // Areas overview
        $this->section('areas', 'areas', [
            'heading' => 'Ons werkgebied rond Bilzen',
            'subheading' => 'Vanuit Bilzen rijden we naar de deelgemeenten, de steden in de buurt en verschillende luchthavens. Klik op een gemeente voor meer info.',
        ]);

        // Airport transfer: link the dedicated Maastricht/Brussels page, and
        // replace a hard-coded Brussels Airport fare (€210, "about 85 km")
        // that contradicted the live popular-route data on the same page.
        $this->section('airport-transfer', 'rich_text', [
            'body' => '<p><strong>Qasim Taxi</strong> biedt betrouwbaar en comfortabel luchthavenvervoer vanuit Bilzen en de omliggende regio. Je wordt aan je deur opgehaald en naar de luchthaven van jouw keuze gebracht, zodat je ontspannen aan je reis kunt beginnen. Of je nu een <strong>taxi van Bilzen naar Brussels Airport</strong>, Charleroi Airport of een andere luchthaven nodig hebt, wij zorgen voor een comfortabele rit. Wil je vooraf weten wat je luchthavenrit kost? Vul je rit in via het boekingsformulier voor een <strong>prijs voor luchthavenvervoer vanuit Bilzen</strong> en reserveer je taxi op het gewenste tijdstip.</p>'
                .'<p>Vlieg je vanaf Maastricht Aachen Airport of Brussels Airport? Lees dan onze praktische uitleg over een <a href="/taxi-maastricht-brussels-airport">taxi van Bilzen naar Maastricht Aachen Airport en Brussels Airport</a>, met tips voor het kiezen van je ophaaltijd.</p>',
        ]);
        $this->faqAnswer('airport-transfer', 'Hoeveel kost een taxi van Bilzen naar Brussels Airport?',
            'De actuele vaste prijs voor de route Bilzen – Brussels Airport (Zaventem) vind je bij de populaire routes op deze pagina. Vertrek je vanaf een ander adres, vul dan je rit in via het boekingsformulier voor een prijsschatting op maat.');
        $this->faqAnswer('airport-transfer', 'Hoe kan ik luchthavenvervoer vanuit Bilzen boeken?',
            'Je kunt je luchthavenvervoer online aanvragen via het boekingsformulier op deze pagina, of bellen of een WhatsApp-bericht sturen naar +32 467 61 48 60.');

        // /city-rides: give it its own H1 so it no longer duplicates
        // /chauffeur-service ("Lokale Taxi in Bilzen – ...").
        $this->section('city-rides', 'hero', [
            'heading' => 'Taxiritten vanuit Bilzen naar Hasselt, Genk, Tongeren en omgeving',
        ]);

        // Legal pages — Dutch versions of the existing English text, same
        // meaning (no new policy terms added).
        $this->section('privacy-policy', 'rich_text', [
            'heading' => 'Privacybeleid',
            'body' => '<p>We verzamelen alleen de gegevens die nodig zijn om onze vervoersdiensten te leveren, zoals je naam, contactgegevens en ophaal- en afzetadres. We verkopen je persoonsgegevens nooit aan derden.</p>'
                .'<p>Je betaalgegevens worden veilig verwerkt via onze betaalproviders en worden nooit op onze servers bewaard.</p>'
                .'<p>Vragen over je gegevens? Neem contact met ons op via onze <a href="/contact">contactpagina</a>.</p>',
        ]);
        $this->section('terms', 'rich_text', [
            'heading' => 'Algemene voorwaarden',
            'body' => '<p>Door een rit bij ons te reserveren, ga je ermee akkoord dat je op tijd aanwezig bent op het afgesproken ophaalpunt. Bij annulering minder dan 24 uur voor het ophaalmoment kan een annuleringskost worden aangerekend.</p>'
                .'<p>We behouden ons het recht voor om passagiers te weigeren die onze gedragsregels niet naleven.</p>'
                .'<p>Lees ook ons <a href="/refund-policy">terugbetalingsbeleid</a> en ons <a href="/privacy-policy">privacybeleid</a>.</p>',
        ]);
        $this->section('refund-policy', 'rich_text', [
            'heading' => 'Terugbetalingsbeleid',
            'body' => '<p>We willen dat je met een gerust gevoel reserveert. Annuleer je meer dan 24 uur voor het geplande ophaalmoment, dan krijg je het volledige bedrag terug via je oorspronkelijke betaalmethode.</p>'
                .'<p>Bij annulering binnen 24 uur voor het ophaalmoment kan een annuleringskost van maximaal 50% van de ritprijs worden aangerekend. Wie niet komt opdagen of annuleert nadat de chauffeur al op het ophaaladres is aangekomen, krijgt geen terugbetaling.</p>'
                .'<p>Goedgekeurde terugbetalingen worden binnen 5 tot 10 werkdagen verwerkt, afhankelijk van je bank of kaartuitgever. Voor een terugbetaling na een probleem met je rit neem je contact op via onze <a href="/contact">contactpagina</a>, met je referentienummer bij de hand.</p>',
        ]);
        $this->section('cookie-policy', 'rich_text', [
            'heading' => 'Cookiebeleid',
            'body' => '<p>Onze website gebruikt cookies en vergelijkbare technieken om je aangemeld te houden, je taal- en valutavoorkeur te onthouden en te begrijpen hoe de site gebruikt wordt, zodat we ze kunnen verbeteren.</p>'
                .'<ul><li><strong>Noodzakelijke cookies</strong> zijn nodig voor basisfuncties zoals reserveren en afrekenen, en kunnen niet worden uitgeschakeld.</li>'
                .'<li><strong>Voorkeurscookies</strong> onthouden keuzes zoals je taal, valuta en licht of donker thema.</li>'
                .'<li><strong>Analytische cookies</strong> helpen ons het bezoek en gebruik van de site te begrijpen, zodat we onze diensten kunnen verbeteren.</li></ul>'
                .'<p>Je kunt cookies op elk moment beheren of verwijderen via de instellingen van je browser. Als je bepaalde cookies uitschakelt, werkt ons reserveringssysteem mogelijk niet volledig.</p>',
        ]);
    }

    /**
     * The airport area descriptions quoted fixed fares (€210, €65, €250,
     * €280...) that no longer match the live Popular Routes prices shown on
     * the same site, and one promised automatic flight tracking. Rewritten
     * in Dutch without hard-coded amounts — the live route price stays the
     * single source of truth.
     */
    private function updateAirportAreas(): void
    {
        $descriptions = [
            'brussels-zaventem-airport' => 'Qasim Taxi brengt je vanuit Bilzen naar Brussels Airport (Zaventem) aan een vaste routeprijs, zonder taximeter. Geef je vluchttijd door, dan plannen we je ophaaltijd met voldoende marge voor het inchecken en de veiligheidscontrole.',
            'antwerp-airport' => 'Vlieg je vanaf Antwerp Airport (Deurne)? We rijden je vanuit Bilzen naar de luchthaven aan een vaste routeprijs. Geef je vluchtnummer door bij het reserveren, dan houden we zo goed mogelijk rekening met wijzigingen in je vluchtschema.',
            'charleroi-airport' => 'Charleroi Airport ligt verder weg, maar met Qasim Taxi rij je ontspannen vanuit Bilzen naar de luchthaven aan een vaste routeprijs, in onze comfortabele Toyota C-HR.',
            'maastricht-aachen-airport' => 'Maastricht Aachen Airport is de dichtstbijzijnde internationale luchthaven vanuit Bilzen en een van onze populairste luchthavenritten. Je rijdt er aan een vaste routeprijs naartoe, van je voordeur tot aan de terminal.',
            'dusseldorf-airport' => 'Voor verre bestemmingen rijden we je vanuit Bilzen naar Düsseldorf Airport aan een vaste routeprijs. Leun achterover, wij nemen de rit over de grens voor onze rekening.',
        ];

        foreach ($descriptions as $slug => $description) {
            DB::table('areas')->where('slug', $slug)->update(['description' => $description, 'updated_at' => now()]);
        }
    }

    private function updateSettings(): void
    {
        DB::table('settings')->update([
            'tagline' => 'Betrouwbare taxi en luchthavenvervoer in Bilzen',
            'meta_title' => 'Qasim Taxi | Taxi reserveren in Bilzen',
            'meta_description' => 'Qasim Taxi is je lokale taxidienst in Bilzen voor ritten in de regio en luchthavenvervoer. Reserveer online met een directe prijsschatting of bel ons.',
            'meta_keywords' => 'taxi Bilzen, taxi reserveren Bilzen, taxidienst Bilzen, luchthavenvervoer Bilzen, taxi Maastricht Aachen Airport, taxi Brussels Airport',
            'updated_at' => now(),
        ]);
    }

    private function upsertDutchTranslations(): void
    {
        $languageId = DB::table('languages')->where('code', 'nl')->value('id');

        if (! $languageId) {
            return;
        }

        $translations = [
            'Monday' => 'Maandag',
            'Tuesday' => 'Dinsdag',
            'Wednesday' => 'Woensdag',
            'Thursday' => 'Donderdag',
            'Friday' => 'Vrijdag',
            'Saturday' => 'Zaterdag',
            'Sunday' => 'Zondag',
            'Open 24 hours' => '24 uur open',
            'Areas' => 'Werkgebied',
            'Back to all posts' => 'Terug naar alle artikels',
            'Image Alt Text' => 'Alt-tekst afbeelding',
            // Booking widget labels that still rendered in English.
            'Drivers for This Vehicle' => 'Chauffeurs voor dit voertuig',
            'Checking...' => 'Controleren...',
            'Offers:' => 'Aanbiedingen:',
            'Taxi from Bilzen to :area' => 'Taxi van Bilzen naar :area',
            'Taxi :area: reserve your ride' => 'Taxi :area: reserveer je rit',
            'Planning your trip?' => 'Je reis aan het plannen?',
            'Read how a taxi from Bilzen to Maastricht Aachen Airport or Brussels Airport works' => 'Lees hoe je een taxi van Bilzen naar Maastricht Aachen Airport of Brussels Airport regelt',
        ];

        foreach ($translations as $key => $value) {
            DB::table('translations')->updateOrInsert(
                ['language_id' => $languageId, 'group' => '*', 'key' => $key],
                ['value' => $value, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    /**
     * Updates the first section of the given type on a page.
     *
     * @param  array<string, mixed>  $data
     */
    private function section(string $slug, string $type, array $data): void
    {
        $pageId = DB::table('pages')->where('slug', $slug)->value('id');

        if (! $pageId) {
            return;
        }

        $id = DB::table('page_sections')->where('page_id', $pageId)->where('type', $type)->orderBy('sort_order')->orderBy('id')->value('id');

        if ($id) {
            DB::table('page_sections')->where('id', $id)->update($data + ['updated_at' => now()]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function addSectionIfMissing(string $slug, string $type, array $data, int $sortOrder): void
    {
        $pageId = DB::table('pages')->where('slug', $slug)->value('id');

        if (! $pageId || DB::table('page_sections')->where('page_id', $pageId)->where('type', $type)->where('heading', $data['heading'])->exists()) {
            return;
        }

        DB::table('page_sections')->insert($data + [
            'page_id' => $pageId,
            'type' => $type,
            'sort_order' => $sortOrder,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Rewrites one FAQ answer (matched by its exact question) on a page,
     * leaving the page's other questions untouched.
     */
    private function faqAnswer(string $slug, string $question, string $answer): void
    {
        $pageId = DB::table('pages')->where('slug', $slug)->value('id');

        foreach (DB::table('page_sections')->where('page_id', $pageId)->where('type', 'faq')->get() as $section) {
            $items = json_decode((string) $section->content, true) ?: [];
            $changed = false;

            foreach ($items as &$item) {
                if (($item['question'] ?? null) === $question) {
                    $item['answer'] = $answer;
                    $changed = true;
                }
            }
            unset($item);

            if ($changed) {
                DB::table('page_sections')->where('id', $section->id)->update([
                    'content' => json_encode($items, JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
