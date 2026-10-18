<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Publishes three Dutch blog articles through the existing blog system
 * (BlogPost records, served at /{slug} by BlogController::show). Featured
 * images live in public/uploads/blogs/ (with a -960 variant for srcset).
 *
 * Copy only uses facts already on the site: the Toyota C-HR (max 4
 * passengers, 2 pieces of luggage), the booking form fields, the
 * phone/WhatsApp number, cash/card payment, flight-number handling and the
 * request → confirmation flow. No prices, travel times or distances are
 * hard-coded; readers are pointed to the live route prices and the
 * booking form's estimate instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        $categoryId = DB::table('blog_categories')->where('slug', 'travel-tips')->value('id')
            ?? DB::table('blog_categories')->orderBy('id')->value('id');

        $base = now()->startOfMinute();

        foreach ($this->posts() as $index => $post) {
            if (DB::table('blog_posts')->where('slug', $post['slug'])->exists()) {
                continue;
            }

            DB::table('blog_posts')->insert($post + [
                'blog_category_id' => $categoryId,
                'author_id' => null,
                'status' => 'published',
                'is_featured' => false,
                'views_count' => 0,
                'robots_index' => true,
                'robots_follow' => true,
                // Staggered so previous/next navigation has a stable order.
                'published_at' => $base->copy()->subMinutes(30 - $index * 10),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Cache::flush();
    }

    public function down(): void
    {
        DB::table('blog_posts')->whereIn('slug', array_column($this->posts(), 'slug'))->delete();
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function posts(): array
    {
        return [
            [
                'slug' => 'taxi-reserveren-in-bilzen',
                'title' => 'Taxi reserveren in Bilzen: zo bereid je je rit goed voor',
                'meta_title' => 'Taxi reserveren in Bilzen: zo bereid je je rit voor',
                'meta_description' => 'Een taxi reserveren in Bilzen? Lees wanneer je best op voorhand boekt, welke gegevens je klaarlegt en hoe je bij Qasim Taxi vlot je rit aanvraagt.',
                'meta_keywords' => 'taxi reserveren Bilzen, taxi boeken in Bilzen, taxidienst Bilzen',
                'excerpt' => 'Wanneer reserveer je een taxi best op voorhand, welke gegevens leg je klaar en hoe verloopt het reserveren bij Qasim Taxi? Praktische tips voor een vlotte rit in en rond Bilzen.',
                'featured_image' => 'taxi-reserveren-bilzen-ophaling.webp',
                'featured_image_alt' => 'Gele taxi met geopende achterdeur haalt een reiziger met koffer op in een straat met Vlaamse rijhuizen',
                'body' => $this->bodyReserveren(),
            ],
            [
                'slug' => 'taxi-van-bilzen-naar-maastricht-aachen-airport',
                'title' => 'Taxi van Bilzen naar Maastricht Aachen Airport: praktische tips voor je vertrek',
                'meta_title' => 'Taxi naar Maastricht Aachen Airport vanuit Bilzen: tips',
                'meta_description' => 'Met de taxi van Bilzen naar Maastricht Aachen Airport? Zo kies je je ophaaltijd, bereid je je reservering voor en regel je meteen je terugrit.',
                'meta_keywords' => 'taxi Bilzen Maastricht Aachen Airport, taxi naar Maastricht Aachen Airport, luchthavenvervoer Bilzen',
                'excerpt' => 'Vlieg je vanaf Maastricht Aachen Airport? Lees hoe je vanuit Bilzen je ophaaltijd berekent, wat je doorgeeft bij je reservering en hoe je ook je terugrit regelt.',
                'featured_image' => 'taxi-bilzen-maastricht-aachen-airport.webp',
                'featured_image_alt' => 'Taxi onderweg naar de luchthaven bij zonsopgang, met opstijgende vliegtuigen en een verkeersbord met een vliegtuigsymbool',
                'body' => $this->bodyMaastricht(),
            ],
            [
                'slug' => 'taxi-van-bilzen-naar-brussels-airport',
                'title' => 'Taxi van Bilzen naar Brussels Airport: zo plan je je luchthavenvervoer',
                'meta_title' => 'Taxi van Bilzen naar Brussels Airport: zo plan je je rit',
                'meta_description' => 'Plan je taxi van Bilzen naar Brussels Airport: kies een veilig ophaaluur, bereid je reservering voor en vraag vooraf je prijs aan bij Qasim Taxi.',
                'meta_keywords' => 'taxi Bilzen Brussels Airport, taxi naar Brussels Airport, luchthaven taxi reserveren',
                'excerpt' => 'Van Bilzen naar Brussels Airport in Zaventem is een flinke rit. Zo kies je een veilig ophaaluur, bereid je je reservering voor en regel je je terugreis zonder stress.',
                'featured_image' => 'taxi-bilzen-brussels-airport-bagage.webp',
                'featured_image_alt' => 'Reizigers met koffers bij een taxi met open koffer voor een verlichte luchthaventerminal, met een klok als symbool voor tijdig vertrekken',
                'body' => $this->bodyBrussels(),
            ],
        ];
    }

    private function bodyReserveren(): string
    {
        return <<<'HTML'
<p>Een taxi die je vooraf reserveert, staat klaar wanneer jij hem nodig hebt. Dat is handig voor een vroege rit naar de luchthaven, maar evengoed voor een afspraak bij de dokter, een etentje in Hasselt of de terugweg na een feest. In dit artikel lees je wanneer reserveren echt loont, welke gegevens je best klaarlegt en hoe je bij Qasim Taxi in Bilzen je rit aanvraagt.</p>

<h2>Wanneer reserveer je een taxi best op voorhand?</h2>
<p>Je kunt ons ook op korte termijn contacteren, maar voor sommige ritten wil je gewoon zekerheid. Reserveer zeker vooraf als:</p>
<ul>
<li><strong>je heel vroeg of heel laat vertrekt</strong>, bijvoorbeeld voor een ochtendvlucht of de rit naar huis na een avond uit;</li>
<li><strong>je op een vast uur ergens moet zijn</strong>, zoals een ziekenhuisafspraak, een sollicitatie, een trein of een zakelijke vergadering;</li>
<li><strong>het een drukke periode is</strong>, zoals een weekend, een feestdag of het begin van de schoolvakantie;</li>
<li><strong>je met bagage of met meerdere personen reist</strong>, zodat we vooraf kunnen bekijken of alles in de wagen past.</li>
</ul>
<p>Hoe vroeger je reserveert, hoe meer kans dat het gewenste uur nog vrij is. Een aanvraag die je online verstuurt, is pas definitief zodra wij ze bevestigen. Plan dus niet op het laatste moment als je rit echt niet mag mislukken.</p>

<h2>Deze gegevens leg je klaar</h2>
<p>Met de juiste informatie bij de hand is je reservering in een paar minuten rond en weet de chauffeur precies waar en wanneer hij moet zijn.</p>

<h3>Ophaaladres</h3>
<p>Geef je volledige adres op, met huisnummer en eventueel busnummer. Woon je in een deelgemeente zoals Munsterbilzen, Beverst of Eigenbilzen, vermeld die dan ook. Moet de chauffeur aan een specifieke ingang of achteraan het gebouw wachten, laat dat dan zeker weten.</p>

<h3>Bestemming</h3>
<p>Hoe preciezer, hoe beter. Bij een ziekenhuis of groot bedrijf scheelt het veel als je weet aan welke ingang je wil uitstappen. Ga je naar de luchthaven, geef dan de juiste luchthaven op, want Maastricht Aachen Airport ligt bijvoorbeeld niet in de stad Maastricht zelf.</p>

<h3>Datum en ophaaluur</h3>
<p>Geef het uur op waarop je <em>opgehaald</em> wil worden, niet het uur waarop je ergens moet zijn. Let ook op ritten rond middernacht: een rit om 00.30 uur in de nacht van vrijdag op zaterdag reserveer je voor zaterdag. Die vergissing gebeurt vaker dan je denkt.</p>

<h3>Passagiers en bagage</h3>
<p>Onze Toyota C-HR biedt plaats aan maximaal 4 passagiers en 2 stuks bagage. Vul het juiste aantal in het boekingsformulier in. Reis je met meer bagage of met iets groots, zoals een rolstoel of een fiets, vraag dan eerst even na of dat lukt.</p>

<h3>Contactgegevens</h3>
<p>Je naam en een telefoonnummer waarop je op de dag van de rit bereikbaar bent, zijn onmisbaar. Een e-mailadres is optioneel.</p>

<h3>Tussenstop of terugrit</h3>
<p>Moet je onderweg iemand oppikken of wil je meteen ook de terugweg regelen? In het boekingsformulier kun je een tussenstop en een retourrit toevoegen, met een eigen datum en uur.</p>

<h2>Speciale wensen doorgeven</h2>
<p>Heb je een bijzondere vraag, zoals hulp bij het in- en uitstappen, extra bagage of een kinderzitje? Vraag dan vooraf telefonisch of via WhatsApp na wat mogelijk is. Zo weet je op voorhand waar je aan toe bent en sta je op de dag zelf niet voor verrassingen.</p>

<h2>Zo reserveer je een taxi bij Qasim Taxi</h2>
<ol>
<li><strong>Vul je rit in.</strong> Open het <a href="/#booking-widget">boekingsformulier op onze homepagina</a> en vul je ophaaladres, bestemming, datum en ophaaluur in.</li>
<li><strong>Bekijk je prijsschatting.</strong> Het formulier berekent meteen een geschatte prijs. Voor populaire routes, zoals <a href="/city-rides">ritten naar Hasselt, Genk, Tongeren en Maastricht</a>, geldt een vaste prijs. De uiteindelijke prijs kan afwijken door bijvoorbeeld wachttijd of extra diensten.</li>
<li><strong>Verstuur je aanvraag.</strong> Je krijgt een referentienummer. Bewaar dat goed; het helpt als je later iets wil wijzigen.</li>
<li><strong>Wacht op de bevestiging.</strong> Je rit staat vast zodra Qasim Taxi je reservering bevestigt.</li>
</ol>
<p>Liever even iemand aan de lijn? Bel of stuur een WhatsApp-bericht naar <a href="tel:+32467614860">+32 467 61 48 60</a>, of laat een bericht achter via onze <a href="/contact">contactpagina</a>.</p>

<h2>Tips voor een vlotte ophaling</h2>
<ul>
<li>Sta een paar minuten voor het afgesproken uur klaar, met je bagage al aan de deur.</li>
<li>Hou je telefoon bij de hand, zodat de chauffeur je kan bereiken als het ophaalpunt niet meteen te vinden is.</li>
<li>Haal je iemand op of woon je in een rustige straat? Een brandende buitenlamp of zichtbaar huisnummer helpt, zeker 's avonds.</li>
<li>Wijzigen je plannen? Laat het ons zo snel mogelijk weten, dan zoeken we samen een oplossing.</li>
<li>Je kunt contant of met je kaart betalen. Laat gewoon je voorkeur weten.</li>
</ul>

<h2>Veelgestelde vragen</h2>
<h3>Kan ik ook vandaag nog een taxi reserveren in Bilzen?</h3>
<p>We proberen ook last-minute aanvragen zoveel mogelijk in te plannen. Bel of stuur een WhatsApp-bericht voor het snelste antwoord.</p>
<h3>Rijdt Qasim Taxi ook buiten Bilzen?</h3>
<p>Ja. We rijden in Bilzen en de deelgemeenten, naar steden in de buurt zoals Hasselt, Genk, Tongeren en Maastricht, en naar verschillende luchthavens. Bekijk ons volledige <a href="/areas">werkgebied</a>.</p>
<h3>Wat als ik mijn rit moet annuleren?</h3>
<p>Neem zo snel mogelijk contact met ons op. In ons <a href="/refund-policy">terugbetalingsbeleid</a> lees je wat er gebeurt bij een annulering. Meer antwoorden vind je bij de <a href="/faq">veelgestelde vragen</a>.</p>

<h2>Je taxi in Bilzen reserveren</h2>
<p>Klaar om je rit vast te leggen? <a href="/#booking-widget">Vul je rit in via het boekingsformulier</a> of bel ons op <a href="tel:+32467614860">+32 467 61 48 60</a>. Moet je naar de luchthaven? Lees dan ook hoe je een <a href="/taxi-maastricht-brussels-airport">taxi van Bilzen naar Maastricht Aachen Airport of Brussels Airport</a> regelt.</p>
HTML;
    }

    private function bodyMaastricht(): string
    {
        return <<<'HTML'
<p>Voor veel reizigers uit Bilzen is Maastricht Aachen Airport de dichtstbijzijnde luchthaven. Vanaf daar vertrekken vooral vakantievluchten, vaak op vroege of late uren. Een taxi brengt je dan van je voordeur tot aan de vertrekhal, zonder parkeerstress en zonder overstappen met je koffers. In dit artikel lees je hoe je je ophaaltijd kiest, wat je doorgeeft bij je reservering en hoe je meteen je terugrit regelt.</p>

<h2>Maastricht Aachen Airport: goed om te weten</h2>
<ul>
<li><strong>De luchthaven ligt niet in Maastricht zelf.</strong> Maastricht Aachen Airport ligt in Beek in Nederlands-Limburg, ten noorden van de stad Maastricht. Kies bij je reservering dus de luchthaven als bestemming en niet gewoon "Maastricht".</li>
<li><strong>Je rijdt over de grens.</strong> De rit gaat van België naar Nederland. Steek je identiteitskaart of paspoort in je handbagage, niet in je koffer.</li>
<li><strong>Het is een vaste route.</strong> Bilzen – Maastricht Aachen Airport staat bij onze populaire routes met een vaste prijs. Het actuele bedrag vind je op onze pagina over een <a href="/taxi-maastricht-brussels-airport">taxi naar Maastricht Aachen Airport en Brussels Airport</a>.</li>
</ul>

<h2>Je ophaaltijd kiezen: reken terug vanaf je vlucht</h2>
<p>De belangrijkste vraag bij een luchthavenrit is: hoe laat moet de taxi voor de deur staan? Begin niet bij het uur van je vlucht, maar bij het moment waarop je op de luchthaven moet zijn.</p>
<ol>
<li><strong>Kijk wat je luchtvaartmaatschappij vraagt.</strong> Op je boekingsbevestiging of de website van je maatschappij staat hoe vroeg je moet inchecken en wanneer de bagagebalie sluit.</li>
<li><strong>Tel de rit erbij.</strong> Vraag ons gerust hoe lang de rit op jouw vertrekuur ongeveer duurt.</li>
<li><strong>Voorzie marge.</strong> Denk aan verkeer, slecht weer, wegenwerken en het inladen van je bagage. Een kwartier extra is vaak het verschil tussen ontspannen en gehaast aankomen.</li>
</ol>
<p>Een voorbeeld: je vlucht vertrekt om 07.00 uur en je maatschappij vraagt je om twee uur voor vertrek aanwezig te zijn. Dan wil je om 05.00 uur op de luchthaven staan. Trek daar de rittijd en je marge van af, en je hebt je ophaaluur.</p>
<p>Let ook op de datum. Een vlucht om 06.00 uur op zaterdag betekent dat je taxi in de vroege ochtend van zaterdag rijdt, niet op vrijdagavond.</p>

<h2>Wat geef je door bij je reservering?</h2>
<ul>
<li>Je volledige ophaaladres in Bilzen of omgeving.</li>
<li>Maastricht Aachen Airport als bestemming.</li>
<li>De datum en het gewenste ophaaluur.</li>
<li>Het aantal passagiers en stuks bagage. Onze Toyota C-HR biedt plaats aan maximaal 4 passagiers en 2 stuks bagage.</li>
<li>Je naam en een gsm-nummer waarop je bereikbaar bent.</li>
<li>Je vluchtnummer. Het boekingsformulier heeft daar geen apart veld voor, dus stuur het na je aanvraag telefonisch of via WhatsApp door.</li>
</ul>

<h2>Reserveren bij Qasim Taxi</h2>
<p>Je vraagt je rit aan via het <a href="/#booking-widget">boekingsformulier</a>: vul je adres, de luchthaven, de datum en het uur in en je ziet meteen een prijsschatting. Na het versturen krijg je een referentienummer. Je rit staat vast zodra Qasim Taxi je reservering bevestigt. Controleer bij die bevestiging nog eens het ophaaluur en het adres.</p>
<p>Liever persoonlijk contact? Bel of stuur een WhatsApp-bericht naar <a href="tel:+32467614860">+32 467 61 48 60</a>. Algemene tips over reserveren lees je in ons artikel <a href="/taxi-reserveren-in-bilzen">Taxi reserveren in Bilzen</a>.</p>

<h2>Je terugrit van Maastricht Aachen Airport naar Bilzen</h2>
<p>Na een vakantie wil je snel naar huis. Je kunt de terugrit meteen mee reserveren door in het boekingsformulier een retourrit toe te voegen, met de datum en het uur van je terugkomst. Je kunt de terugrit ook later apart aanvragen.</p>
<ul>
<li>Geef je vluchtnummer en de geplande landingstijd door. Heeft je vlucht vertraging, dan passen we je ophaaltijd zo goed mogelijk aan.</li>
<li>Spreek vooraf af waar de chauffeur op je wacht.</li>
<li>Zet je telefoon aan zodra je geland bent, zodat we je kunnen bereiken.</li>
<li>Reken wat tijd voor de bagageband en eventuele controles.</li>
</ul>

<h2>Checklist voor je vertrek</h2>
<ul>
<li>Identiteitskaart of paspoort en instapkaart in je handbagage.</li>
<li>Gsm opgeladen en je vluchtnummer doorgegeven.</li>
<li>Ophaaluur en adres gecontroleerd in je bevestiging.</li>
<li>Koffers klaar aan de deur een paar minuten voor de afgesproken tijd.</li>
<li>Betaalvoorkeur doorgegeven: contant of met de kaart.</li>
</ul>

<h2>Veelgestelde vragen</h2>
<h3>Hoe vroeg reserveer ik een taxi naar Maastricht Aachen Airport?</h3>
<p>Zodra je je vluchtgegevens kent. Vooral voor vluchten vroeg in de ochtend en in de vakantieperiodes is tijdig reserveren verstandig.</p>
<h3>Kunnen we met meer dan 4 personen reizen?</h3>
<p>In onze Toyota C-HR is plaats voor maximaal 4 passagiers. Reis je met een grotere groep, neem dan vooraf contact op zodat we kunnen bekijken wat mogelijk is.</p>
<h3>Ligt de prijs vooraf vast?</h3>
<p>Voor de route Bilzen – Maastricht Aachen Airport geldt een vaste prijs. Vertrek je vanaf een ander adres, dan geeft het boekingsformulier een prijsschatting. Bijvoorbeeld wachttijd of extra diensten kunnen de uiteindelijke prijs beïnvloeden.</p>

<h2>Je luchthaventaxi aanvragen</h2>
<p>Klaar voor vertrek? Bekijk de actuele prijs en vraag je rit aan op onze pagina over een <a href="/taxi-maastricht-brussels-airport">taxi van Bilzen naar Maastricht Aachen Airport</a>, of <a href="/contact">neem contact met ons op</a>. Vlieg je toch vanaf Zaventem? Lees dan onze tips voor een <a href="/taxi-van-bilzen-naar-brussels-airport">taxi van Bilzen naar Brussels Airport</a>.</p>
HTML;
    }

    private function bodyBrussels(): string
    {
        return <<<'HTML'
<p>Brussels Airport in Zaventem is de grootste luchthaven van België, met vluchten naar bestemmingen over de hele wereld. Vanuit Bilzen is het wel een flinke rit: je rijdt door Limburg en Vlaams-Brabant tot aan de rand van Brussel. Met het openbaar vervoer betekent dat al snel overstappen met je koffers. Een taxi brengt je rechtstreeks van je voordeur naar de vertrekhal. Hieronder lees je hoe je die rit goed plant.</p>

<h2>Waarom met de taxi naar Brussels Airport?</h2>
<ul>
<li><strong>Geen overstappen.</strong> Je koffers gaan aan je deur in de wagen en komen er pas aan de luchthaven weer uit.</li>
<li><strong>Geen parkeerplaats zoeken.</strong> Je hoeft je auto niet dagen of weken op een luchthavenparking achter te laten.</li>
<li><strong>Vertrekken wanneer het jou past.</strong> Ook op uren waarop het openbaar vervoer nog weinig rijdt, vraag je gewoon een rit aan op het uur dat je nodig hebt.</li>
<li><strong>Handig met kinderen of veel bagage.</strong> Iedereen stapt samen in, zonder gesleep op perrons.</li>
</ul>

<h2>Zo kies je het juiste ophaaluur</h2>
<p>Omdat Brussels Airport verder van Bilzen ligt dan Maastricht Aachen Airport, weegt de planning hier zwaarder. Een kleine vertraging onderweg heeft meteen gevolgen voor je check-in.</p>
<h3>Begin bij het advies van je maatschappij en de luchthaven</h3>
<p>Je luchtvaartmaatschappij vermeldt hoe vroeg je moet inchecken en je bagage moet afgeven. Ook Brussels Airport geeft op zijn website advies over hoe vroeg je aanwezig moet zijn. Neem het ruimste van beide als vertrekpunt.</p>
<h3>Hou rekening met verkeer rond Brussel</h3>
<p>Tijdens de ochtend- en avondspits staan de autosnelwegen richting Brussel vaak vast. Vertrek je op een weekdag tussen het begin van de ochtend en het einde van de spits, plan dan extra tijd in. In de schoolvakanties is het bovendien drukker op de luchthaven zelf.</p>
<h3>Vraag ons naar de rittijd</h3>
<p>Hoe lang de rit duurt, hangt af van het uur en de dag. Laat ons je vluchttijd weten, dan denken we mee over een haalbaar ophaaluur.</p>

<h2>Je reservering voorbereiden</h2>
<table>
<thead><tr><th>Gegeven</th><th>Waarom het belangrijk is</th></tr></thead>
<tbody>
<tr><td>Ophaaladres</td><td>Met huisnummer en eventueel busnummer, zodat de chauffeur je meteen vindt.</td></tr>
<tr><td>Bestemming: Brussels Airport (Zaventem)</td><td>Zo is er geen verwarring met andere Brusselse adressen of luchthavens.</td></tr>
<tr><td>Datum en ophaaluur</td><td>Het uur waarop je wil vertrekken, niet het uur van je vlucht. Let op bij nachtritten.</td></tr>
<tr><td>Passagiers en bagage</td><td>Onze Toyota C-HR biedt plaats aan maximaal 4 passagiers en 2 stuks bagage.</td></tr>
<tr><td>Telefoonnummer</td><td>Zodat we je op de dag zelf kunnen bereiken.</td></tr>
<tr><td>Vluchtnummer</td><td>Stuur het telefonisch of via WhatsApp door; zeker belangrijk voor je terugrit.</td></tr>
<tr><td>Terugrit</td><td>Voeg ze meteen toe in het formulier of reserveer ze later apart.</td></tr>
</tbody>
</table>

<h2>Prijs aanvragen en reserveren</h2>
<p>Bilzen – Brussels Airport is een van onze vaste routes. De actuele prijs vind je op onze pagina over een <a href="/taxi-maastricht-brussels-airport">taxi naar Brussels Airport en Maastricht Aachen Airport</a>. Vertrek je vanaf een ander adres, of wil je onderweg nog iemand oppikken, vul dan je rit in via het <a href="/#booking-widget">boekingsformulier</a>. Daar kun je ook een tussenstop toevoegen en zie je meteen een prijsschatting. Houd er rekening mee dat bijvoorbeeld wachttijd, tol of extra diensten de uiteindelijke prijs kunnen beïnvloeden.</p>
<p>Na het versturen van je aanvraag krijg je een referentienummer. Je rit staat vast zodra Qasim Taxi je reservering bevestigt. Controleer dan meteen het ophaaluur, de datum en het adres. Heb je liever eerst een prijs voor een rit op maat? Bel of stuur een WhatsApp-bericht naar <a href="tel:+32467614860">+32 467 61 48 60</a>, of gebruik onze <a href="/contact">contactpagina</a>.</p>

<h2>De terugreis vanaf Brussels Airport</h2>
<p>Ook voor de terugweg naar Bilzen kun je op ons rekenen. Voeg de retourrit toe bij je reservering, met de datum en het geplande uur van je landing. Geef je vluchtnummer door, zodat we je ophaaltijd zo goed mogelijk kunnen aanpassen als je vlucht vertraging heeft. Spreek vooraf af waar je de chauffeur ontmoet, en hou er rekening mee dat het even kan duren voor je je koffers van de bagageband hebt.</p>

<h2>Nog enkele tips voor je reisplanning</h2>
<ul>
<li>Check online in als je luchtvaartmaatschappij dat toelaat. Zo win je tijd op de luchthaven.</li>
<li>Weeg je koffers thuis. Dat voorkomt verrassingen aan de balie.</li>
<li>Reis je met een groep? Spreek met iedereen hetzelfde vertrekpunt en uur af, of vraag na of meerdere ophaaladressen mogelijk zijn.</li>
<li>Hou je reisdocumenten en medicatie in je handbagage.</li>
</ul>

<h2>Veelgestelde vragen</h2>
<h3>Kan ik een taxi naar Brussels Airport reserveren voor een vroege vlucht?</h3>
<p>Ja, je kunt een rit vroeg in de ochtend aanvragen. Reserveer zo vroeg mogelijk. Zodra we je reservering bevestigen, weet je zeker dat je rit ingepland is.</p>
<h3>Hoeveel kost een taxi van Bilzen naar Brussels Airport?</h3>
<p>Voor de route Bilzen – Brussels Airport geldt een vaste prijs. Het actuele bedrag staat bij de populaire routes op onze pagina over een <a href="/taxi-maastricht-brussels-airport">taxi naar Brussels Airport</a>.</p>
<h3>Kan ik met de kaart betalen?</h3>
<p>Ja, je kunt contant of met je kaart betalen. Geef je voorkeur gewoon door bij het reserveren.</p>

<h2>Vlot naar Zaventem</h2>
<p>Een goede rit naar de luchthaven begint met een goed gekozen ophaaluur. <a href="/#booking-widget">Vraag je taxi naar Brussels Airport aan</a> of bel ons op <a href="tel:+32467614860">+32 467 61 48 60</a>. Vlieg je liever vanaf een luchthaven dichter bij huis? Lees dan onze tips voor een <a href="/taxi-van-bilzen-naar-maastricht-aachen-airport">taxi naar Maastricht Aachen Airport</a>.</p>
HTML;
    }
};
