<?php

namespace Database\Seeders;

use App\Models\PrivacyPolicyPage;
use Illuminate\Database\Seeder;

class PrivacyPolicyPageSeeder extends Seeder
{
    public const INITIAL_LAST_EDITED_AT = '2026-01-25 12:00:00';

    public function run(): void
    {
        PrivacyPolicyPage::query()->firstOrCreate(
            ['id' => 1],
            [
                'title' => 'Privacy Policy',
                'content' => self::initialContent(),
                'created_at' => self::INITIAL_LAST_EDITED_AT,
                'updated_at' => self::INITIAL_LAST_EDITED_AT,
            ],
        );
    }

    public static function initialContent(): string
    {
        return <<<'HTML'
<h2>Website visitors</h2>
<p>Like most website operators, Nanocosmetech Inc. collects non-personally-identifying information of the sort that web browsers and servers typically make available, such as the browser type, language preference, referring site, and the date and time of each visitor request. Nanocosmetech Inc.’s purpose in collecting non-personally identifying information is to better understand how visitors use its websites. From time to time, Nanocosmetech Inc. may release non-personally-identifying information in the aggregate, e.g., by publishing a report on trends in the usage of its website. Nanocosmetech Inc. also collects potentially personally-identifying information like Internet Protocol (IP) addresses for logged-in users and for users leaving comments on <a href="https://www.forlled.com">www.forlled.com</a> blog posts. Nanocosmetech Inc. only discloses logged-in user and commenter IP addresses under the same circumstances that it uses and discloses personally-identifying information as described below.</p>

<h2>Gathering of personally-identifying information</h2>
<p>Certain visitors to Nanocosmetech Inc.’s websites choose to interact in ways that require Nanocosmetech Inc. to gather personally-identifying information. The amount and type of information that Nanocosmetech Inc. gathers depends on the nature of the interaction. For example, we ask visitors who sign up for an account at <a href="https://www.forlled.com">www.forlled.com</a> to provide a username and email address.</p>

<h2>Security</h2>
<p>The security of your personal information is important to us, but remember that no method of transmission over the Internet, or method of electronic storage, is 100% secure. While we strive to use commercially acceptable means to protect your personal information, we cannot guarantee its absolute security.</p>

<h2>Links to external sites</h2>
<p>Our service may contain links to external sites that are not operated by us. If you click a third-party link, you will be directed to that third party’s site. We strongly advise you to review the privacy policy and terms of every site you visit. We have no control over, and assume no responsibility for, the content, privacy policies, or practices of any third-party sites, products, or services.</p>

<h2>Google Ads remarketing</h2>
<p><a href="https://www.forlled.com">www.forlled.com</a> may use remarketing services to advertise on third-party websites (including Google) to previous visitors of our site. Third-party vendors, including Google, use cookies to serve ads based on someone’s past visits to our website. You can set preferences for how Google advertises to you using the Google Ad Preferences page, and if you want to you can opt out of interest-based advertising entirely by cookie settings or permanently using a browser plugin.</p>

<h2>Protection of certain personally-identifying information</h2>
<p>Nanocosmetech Inc. discloses potentially personally-identifying and personally-identifying information only to those of its employees, contractors, and affiliated organizations that (i) need to know that information in order to process it on Nanocosmetech Inc.’s behalf or to provide services available at Nanocosmetech Inc.’s websites, and (ii) that have agreed not to disclose it to others. Some of those employees, contractors and affiliated organizations may be located outside of your home country; by using Nanocosmetech Inc.’s websites, you consent to the transfer of such information to them. Nanocosmetech Inc. will not rent or sell potentially personally-identifying and personally-identifying information to anyone. Other than to its employees, contractors and affiliated organizations, as described above, Nanocosmetech Inc. discloses potentially personally-identifying and personally-identifying information only in response to a subpoena, court order or other governmental request, or when Nanocosmetech Inc. believes in good faith that disclosure is reasonably necessary to protect the property or rights of Nanocosmetech Inc., third parties or the public at large. If you are a registered user of <a href="https://www.forlled.com">www.forlled.com</a> and have supplied your email address, Nanocosmetech Inc. may occasionally send you an email to tell you about new features, solicit your feedback, or just keep you up to date with what’s going on with Nanocosmetech Inc. and our products.</p>

<h2>Aggregated statistics</h2>
<p>Nanocosmetech Inc. may collect statistics about the behavior of visitors to its websites. Nanocosmetech Inc. may display this information publicly or provide it to others. However, Nanocosmetech Inc. does not disclose your personally-identifying information other than as described in this policy.</p>

<h2>Cookies</h2>
<p>To enrich and perfect your online experience, Nanocosmetech Inc. uses “Cookies”, similar technologies and services provided by others to display personalized content, appropriate advertising and store your preferences on your computer.</p>
<p>A cookie is a string of information that a website stores on a visitor’s computer, and that the visitor’s browser provides to the website each time the visitor returns. Nanocosmetech Inc. uses cookies to help Nanocosmetech Inc. identify and track visitors, their usage of <a href="https://www.forlled.com">www.forlled.com</a>, and their website access preferences. Nanocosmetech Inc. visitors who do not wish to have cookies placed on their computers should set their browsers to refuse cookies before using Nanocosmetech Inc.’s websites, with the drawback that certain features of Nanocosmetech Inc.’s websites may not function properly without the aid of cookies. By continuing to navigate our website without changing your cookie settings, you hereby acknowledge and agree to Nanocosmetech Inc.’s use of cookies.</p>

<h2>Privacy policy changes</h2>
<p>Although most changes are likely to be minor, Nanocosmetech Inc. may change its Privacy Policy from time to time, and in Nanocosmetech Inc.’s sole discretion. Nanocosmetech Inc. encourages visitors to frequently check this document for any changes to its Privacy Policy. Your continued use of this site after any change to this Privacy Policy will constitute your acceptance of such change.</p>
HTML;
    }
}
