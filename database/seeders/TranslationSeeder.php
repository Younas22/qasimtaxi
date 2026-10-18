<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Translation;
use Illuminate\Database\Seeder;

class TranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lines = [
            'Admin Panel' => ['ar' => 'لوحة التحكم', 'ur' => 'ایڈمن پینل', 'de' => 'Admin-Bereich'],
            'Dashboard' => ['ar' => 'لوحة القيادة', 'ur' => 'ڈیش بورڈ', 'de' => 'Übersicht'],
            'Bookings' => ['ar' => 'الحجوزات', 'ur' => 'بکنگز', 'de' => 'Buchungen'],
            'Vehicles' => ['ar' => 'المركبات', 'ur' => 'گاڑیاں', 'de' => 'Fahrzeuge'],
            'Drivers' => ['ar' => 'السائقون', 'ur' => 'ڈرائیورز', 'de' => 'Fahrer'],
            'Customers' => ['ar' => 'العملاء', 'ur' => 'کسٹمرز', 'de' => 'Kunden'],
            'Roles & Permissions' => ['ar' => 'الأدوار والصلاحيات', 'ur' => 'کردار اور اجازتیں', 'de' => 'Rollen & Berechtigungen'],
            'Languages' => ['ar' => 'اللغات', 'ur' => 'زبانیں', 'de' => 'Sprachen'],
            'Settings' => ['ar' => 'الإعدادات', 'ur' => 'ترتیبات', 'de' => 'Einstellungen'],
            'Soon' => ['ar' => 'قريباً', 'ur' => 'جلد', 'de' => 'Bald'],
            'My Profile' => ['ar' => 'ملفي الشخصي', 'ur' => 'میری پروفائل', 'de' => 'Mein Profil'],
            'Sign Out' => ['ar' => 'تسجيل الخروج', 'ur' => 'سائن آؤٹ', 'de' => 'Abmelden'],
            'Notifications' => ['ar' => 'الإشعارات', 'ur' => 'اطلاعات', 'de' => 'Benachrichtigungen'],
            'No notifications' => ['ar' => 'لا توجد إشعارات', 'ur' => 'کوئی اطلاع نہیں', 'de' => 'Keine Benachrichtigungen'],
            'All rights reserved.' => ['ar' => 'جميع الحقوق محفوظة.', 'ur' => 'جملہ حقوق محفوظ ہیں۔', 'de' => 'Alle Rechte vorbehalten.'],
            'Crafted for the road ahead.' => ['ar' => 'صُمم من أجل الطريق القادم.', 'ur' => 'آگے کے سفر کے لیے تیار کیا گیا۔', 'de' => 'Gestaltet für den Weg voraus.'],
            'Welcome back' => ['ar' => 'مرحباً بعودتك', 'ur' => 'خوش آمدید', 'de' => 'Willkommen zurück'],
            'Sign in to manage your fleet.' => ['ar' => 'سجّل الدخول لإدارة أسطولك.', 'ur' => 'اپنے فلیٹ کا نظم کرنے کے لیے سائن ان کریں۔', 'de' => 'Melden Sie sich an, um Ihre Flotte zu verwalten.'],
            'Email Address' => ['ar' => 'البريد الإلكتروني', 'ur' => 'ای میل ایڈریس', 'de' => 'E-Mail-Adresse'],
            'Password' => ['ar' => 'كلمة المرور', 'ur' => 'پاس ورڈ', 'de' => 'Passwort'],
            'Forgot password?' => ['ar' => 'هل نسيت كلمة المرور؟', 'ur' => 'پاس ورڈ بھول گئے؟', 'de' => 'Passwort vergessen?'],
            'Remember me' => ['ar' => 'تذكرني', 'ur' => 'مجھے یاد رکھیں', 'de' => 'Angemeldet bleiben'],
            'Sign In' => ['ar' => 'تسجيل الدخول', 'ur' => 'سائن ان', 'de' => 'Anmelden'],
            'Overview' => ['ar' => 'نظرة عامة', 'ur' => 'جائزہ', 'de' => 'Überblick'],
            'Welcome back, :name. Here\'s what\'s happening today.' => [
                'ar' => 'مرحباً بعودتك، :name. إليك ما يحدث اليوم.',
                'ur' => ':name، خوش آمدید۔ آج کی تازہ ترین صورتحال یہ ہے۔',
                'de' => 'Willkommen zurück, :name. Das passiert heute.',
            ],
            'Revenue' => ['ar' => 'الإيرادات', 'ur' => 'آمدنی', 'de' => 'Umsatz'],
            'Recent Bookings' => ['ar' => 'أحدث الحجوزات', 'ur' => 'حالیہ بکنگز', 'de' => 'Letzte Buchungen'],
            'Booking #' => ['ar' => 'رقم الحجز', 'ur' => 'بکنگ نمبر', 'de' => 'Buchungs-Nr.'],
            'Customer' => ['ar' => 'العميل', 'ur' => 'کسٹمر', 'de' => 'Kunde'],
            'Driver' => ['ar' => 'السائق', 'ur' => 'ڈرائیور', 'de' => 'Fahrer'],
            'Pickup' => ['ar' => 'الاستلام', 'ur' => 'پک اپ', 'de' => 'Abholung'],
            'Status' => ['ar' => 'الحالة', 'ur' => 'حیثیت', 'de' => 'Status'],
            'Fare' => ['ar' => 'الأجرة', 'ur' => 'کرایہ', 'de' => 'Fahrpreis'],
            'No bookings yet.' => ['ar' => 'لا توجد حجوزات بعد.', 'ur' => 'ابھی تک کوئی بکنگ نہیں۔', 'de' => 'Noch keine Buchungen.'],

            // 404 page (resources/views/errors/404.blade.php) — added for nl/fr/ar,
            // the site's fully-translated languages (see their ~1,478/1,368-row
            // coverage), so the error page isn't the one place still in English.
            'Page Not Found' => ['nl' => 'Pagina niet gevonden', 'fr' => 'Page introuvable', 'ar' => 'الصفحة غير موجودة'],
            "This page doesn't exist" => ['nl' => 'Deze pagina bestaat niet', 'fr' => "Cette page n'existe pas", 'ar' => 'هذه الصفحة غير موجودة'],
            "The page you're looking for may have moved or no longer exists. Here are a few places to start instead:" => [
                'nl' => 'De pagina die u zoekt is mogelijk verplaatst of bestaat niet meer. Hier zijn een paar plekken om opnieuw te beginnen:',
                'fr' => "La page que vous recherchez a peut-être été déplacée ou n'existe plus. Voici quelques pages pour recommencer :",
                'ar' => 'ربما تم نقل الصفحة التي تبحث عنها أو لم تعد موجودة. إليك بعض الأماكن للبدء من جديد:',
            ],
            // Also fills a pre-existing gap for the same page's "Areas" button link.
            'Areas' => ['nl' => 'Gebieden', 'fr' => 'Zones', 'ar' => 'المناطق'],
        ];

        $languages = Language::whereIn('code', ['en', 'ar', 'ur', 'de', 'nl', 'fr'])->get()->keyBy('code');

        if ($languages->isEmpty()) {
            return;
        }

        foreach ($lines as $key => $translations) {
            $translations['en'] = $key;

            foreach ($translations as $code => $value) {
                if (! isset($languages[$code])) {
                    continue;
                }

                Translation::updateOrCreate(
                    ['language_id' => $languages[$code]->id, 'group' => '*', 'key' => $key],
                    ['value' => $value]
                );
            }
        }
    }
}
