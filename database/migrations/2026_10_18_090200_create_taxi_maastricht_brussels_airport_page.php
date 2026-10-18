<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The dedicated landing page at /taxi-maastricht-brussels-airport, built
 * from the same Page + PageSection records as every other page so it is
 * fully editable under Admin → Pages. Its slug is registered in
 * Page::PAGES / Page::LANDING_PAGES (service-page layout with the booking
 * widget, but no sitewide menu link).
 *
 * Prices are never written into the copy: the live "routes" section shows
 * the current fixed route prices, and the booking widget gives an estimate.
 */
return new class extends Migration
{
    private const SLUG = 'taxi-maastricht-brussels-airport';

    public function up(): void
    {
        if (DB::table('pages')->where('slug', self::SLUG)->exists()) {
            return;
        }

        $pageId = DB::table('pages')->insertGetId([
            'slug' => self::SLUG,
            'name' => 'Taxi naar Maastricht Aachen Airport & Brussels Airport',
            'meta_title' => 'Taxi Bilzen naar Maastricht Aachen Airport & Brussels Airport',
            'meta_description' => 'Taxi van Bilzen naar Maastricht Aachen Airport of Brussels Airport nodig? Qasim Taxi haalt je thuis op. Bekijk de vaste routeprijzen en reserveer online.',
            'robots_index' => true,
            'robots_follow' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Reuse the existing airport-transfer banner/CTA images and hero
        // buttons (call + pricing) so the page matches the service pages.
        $airportPageId = DB::table('pages')->where('slug', 'airport-transfer')->value('id');
        $airportHero = DB::table('page_sections')->where('page_id', $airportPageId)->where('type', 'hero')->first();
        $airportCta = DB::table('page_sections')->where('page_id', $airportPageId)->where('type', 'cta')->first();

        $sections = [
            [
                'type' => 'hero',
                'heading' => 'Taxi van Bilzen naar Maastricht Aachen Airport en Brussels Airport',
                'subheading' => 'We halen je thuis op in Bilzen of omgeving en rijden je naar Maastricht Aachen Airport of Brussels Airport. Vul hieronder je rit in voor een directe prijsschatting.',
                'image' => $airportHero?->image,
                'content' => $airportHero?->content,
            ],
            [
                'type' => 'rich_text',
                'heading' => 'Je luchthaventaxi vanuit Bilzen, van deur tot terminal',
                'body' => '<p>Met een taxi naar de luchthaven begin je je reis zonder gedoe met parkeren, overstappen of zware koffers op de trein. Qasim Taxi is een lokale taxidienst uit Bilzen: we halen je op aan je eigen adres en zetten je af bij de vertrekhal van je luchthaven. Op deze pagina lees je hoe dat werkt voor de twee luchthavens waar reizigers uit Bilzen het vaakst naartoe willen: <strong>Maastricht Aachen Airport</strong> en <strong>Brussels Airport</strong>.</p>'
                    .'<p>Je reserveert via het boekingsformulier bovenaan deze pagina, telefonisch of via WhatsApp op <a href="tel:+32467614860">+32 467 61 48 60</a>. Ook de terugrit kun je meteen regelen: in het formulier voeg je een retourrit toe met de datum en het uur waarop je terug bent.</p>'
                    .'<p><em>Qasim Taxi is een onafhankelijke taxidienst en heeft geen balie op of samenwerking met Maastricht Aachen Airport of Brussels Airport. Het ophaalpunt spreken we vooraf met je af.</em></p>',
            ],
            [
                'type' => 'rich_text',
                'heading' => 'Taxi van Bilzen naar Maastricht Aachen Airport',
                'body' => '<p>Maastricht Aachen Airport is de luchthaven die het dichtst bij Bilzen ligt. Let op: de luchthaven ligt niet in de stad Maastricht zelf, maar in Beek in Nederlands-Limburg, ten noorden van Maastricht. Moet je naar het centrum van Maastricht, dan kies je gewoon dat adres als bestemming.</p>'
                    .'<h3>Waar moet je op letten?</h3>'
                    .'<ul><li><strong>Vertrektijd:</strong> kijk bij je luchtvaartmaatschappij hoe laat je uiterlijk moet inchecken en je bagage moet afgeven, en reken je ophaaltijd daar vanaf terug. Plan wat extra marge in voor verkeer.</li>'
                    .'<li><strong>Over de grens:</strong> de rit gaat van België naar Nederland. Neem je reisdocumenten mee in je handbagage, niet in je koffer.</li>'
                    .'<li><strong>Prijs:</strong> Bilzen – Maastricht Aachen Airport is een van onze vaste populaire routes. De actuele prijs zie je hieronder bij de populaire routes.</li></ul>',
            ],
            [
                'type' => 'rich_text',
                'heading' => 'Taxi van Bilzen naar Brussels Airport',
                'body' => '<p>Brussels Airport in Zaventem is de grootste luchthaven van België en een stuk verder van Bilzen dan Maastricht Aachen Airport. Juist voor die langere rit is een taxi handig: je hoeft niet over te stappen in Brussel en je koffers gaan van je voordeur rechtstreeks mee tot aan de vertrekhal.</p>'
                    .'<h3>Waar moet je op letten?</h3>'
                    .'<ul><li><strong>Verkeer rond Brussel:</strong> tijdens de ochtend- en avondspits kan het druk zijn op de wegen richting de luchthaven. Kies je ophaaltijd daarom ruim, zeker bij een vlucht in de ochtend.</li>'
                    .'<li><strong>Inchecken en veiligheidscontrole:</strong> volg het advies van je luchtvaartmaatschappij en de luchthaven over hoe vroeg je aanwezig moet zijn.</li>'
                    .'<li><strong>Prijs:</strong> ook Bilzen – Brussels Airport is een vaste route. Vertrek je vanaf een ander adres, dan berekent het boekingsformulier een prijsschatting voor jouw rit.</li></ul>',
            ],
            [
                'type' => 'routes',
                'heading' => 'Vaste prijzen voor populaire routes vanuit Bilzen',
                'subheading' => 'De actuele prijzen, onder meer voor Bilzen – Maastricht Aachen Airport en Bilzen – Brussels Airport.',
                'content' => json_encode(['limit' => 20]),
            ],
            [
                'type' => 'rich_text',
                'heading' => 'Zo reserveer je je taxi naar de luchthaven',
                'body' => '<ol>'
                    .'<li><strong>Vul je rit in.</strong> Geef in het <a href="#booking-widget">boekingsformulier</a> je ophaaladres, de luchthaven als bestemming, de datum en het ophaaluur in, samen met het aantal passagiers en stuks bagage. Voeg indien nodig een retourrit toe.</li>'
                    .'<li><strong>Bekijk de prijsschatting.</strong> Je ziet meteen een geschatte prijs voor je rit. De uiteindelijke prijs kan afwijken door bijvoorbeeld wachttijd of extra diensten.</li>'
                    .'<li><strong>Verstuur je aanvraag.</strong> Je krijgt een referentienummer. Je rit is definitief zodra Qasim Taxi je reservering bevestigt.</li>'
                    .'<li><strong>Bezorg ons je vluchtgegevens.</strong> Stuur je vluchtnummer en eventuele bijzonderheden telefonisch of via WhatsApp door, zodat we zo goed mogelijk rekening kunnen houden met je vlucht.</li>'
                    .'</ol>'
                    .'<p>Meer tips lees je in onze blogartikels over een <a href="/taxi-van-bilzen-naar-maastricht-aachen-airport">taxi naar Maastricht Aachen Airport</a> en het <a href="/taxi-van-bilzen-naar-brussels-airport">plannen van je rit naar Brussels Airport</a>. Vlieg je vanaf een andere luchthaven, zoals Antwerpen, Charleroi, Eindhoven of Düsseldorf? Bekijk dan ons algemene <a href="/airport-transfer">luchthavenvervoer vanuit Bilzen</a>.</p>',
            ],
            [
                'type' => 'fleet',
                'heading' => 'Ons voertuig voor je luchthavenrit',
                'subheading' => 'Een comfortabele Toyota C-HR met plaats voor maximaal 4 passagiers en 2 stuks bagage.',
                'content' => json_encode(['limit' => 6, 'category_id' => null]),
            ],
            [
                'type' => 'faq',
                'heading' => 'Veelgestelde vragen over een taxi naar Maastricht Aachen Airport en Brussels Airport',
                'content' => json_encode([
                    ['question' => 'Hoe reserveer ik een taxi van Bilzen naar Maastricht Aachen Airport of Brussels Airport?', 'answer' => 'Vul in het boekingsformulier op deze pagina je ophaaladres, de luchthaven als bestemming, de datum en het ophaaluur in. Je ziet meteen een prijsschatting en verstuurt daarna je aanvraag. Je kunt ook bellen of een WhatsApp-bericht sturen naar +32 467 61 48 60.', 'category' => null],
                    ['question' => 'Wat kost een taxi van Bilzen naar de luchthaven?', 'answer' => 'Voor ritten vanuit Bilzen naar Maastricht Aachen Airport en Brussels Airport geldt een vaste routeprijs; de actuele bedragen staan bij de populaire routes op deze pagina. Vertrek je vanaf een ander adres, dan geeft het boekingsformulier een prijsschatting voor jouw rit.', 'category' => null],
                    ['question' => 'Hoe laat moet ik vertrekken naar de luchthaven?', 'answer' => 'Dat hangt af van je vlucht. Kijk bij je luchtvaartmaatschappij hoe vroeg je moet inchecken en je bagage moet afgeven, en plan daarbovenop marge voor verkeer. Laat ons je vluchttijd weten, dan denken we mee over een geschikte ophaaltijd.', 'category' => null],
                    ['question' => 'Kan ik ook een taxi van de luchthaven terug naar Bilzen reserveren?', 'answer' => 'Ja. Voeg in het boekingsformulier een retourrit toe met de datum en het uur van je terugkomst, of reserveer de terugrit apart. Geef ook je vluchtnummer door, zodat we rekening kunnen houden met je aankomsttijd.', 'category' => null],
                    ['question' => 'Hoeveel passagiers en bagage kunnen mee?', 'answer' => 'Onze Toyota C-HR biedt plaats aan maximaal 4 passagiers en 2 stuks bagage. Reis je met meer bagage, vermeld dat dan vooraf zodat we samen kunnen bekijken wat mogelijk is.', 'category' => null],
                    ['question' => 'Ligt Maastricht Aachen Airport in Maastricht?', 'answer' => 'Nee. Maastricht Aachen Airport ligt in Beek in Nederlands-Limburg, ten noorden van de stad Maastricht. Moet je naar Maastricht zelf, kies dan je adres in Maastricht als bestemming.', 'category' => null],
                    ['question' => 'Heeft Qasim Taxi een balie op de luchthaven?', 'answer' => 'Nee. Qasim Taxi is een onafhankelijke taxidienst uit Bilzen zonder balie op of samenwerking met de luchthavens. Het ophaal- of afzetpunt spreken we bij je reservering met je af.', 'category' => null],
                ], JSON_UNESCAPED_UNICODE),
            ],
            [
                'type' => 'cta',
                'heading' => 'Klaar om je luchthaventaxi te reserveren?',
                'subheading' => 'Vraag je rit naar Maastricht Aachen Airport of Brussels Airport aan, of neem contact met ons op met je vragen.',
                'image' => $airportCta?->image,
                'button_text' => 'Neem contact op',
                'button_url' => '/contact',
            ],
        ];

        foreach ($sections as $sortOrder => $section) {
            DB::table('page_sections')->insert($section + [
                'page_id' => $pageId,
                'sort_order' => $sortOrder,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Cache::flush();
    }

    public function down(): void
    {
        $pageId = DB::table('pages')->where('slug', self::SLUG)->value('id');

        if ($pageId) {
            DB::table('page_sections')->where('page_id', $pageId)->delete();
            DB::table('pages')->where('id', $pageId)->delete();
        }
    }
};
