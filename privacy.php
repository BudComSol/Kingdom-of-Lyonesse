<?php
require_once __DIR__ . '/core/func.php';

head("Privacy Policy", $uid);
?>
<div class="tophead">
    <b>Privacy Policy</b><br><br>
    This policy explains what information Kingdom of Lyonesse collects and how it is used when you visit or play the game.
</div>

<div class="tophead">
    <b>Information We Process</b><br><br>
    When you create or use a hero, the game processes your hero name, account credentials, and gameplay information such as character progress, settings, messages, and activity needed to provide the service. Passwords are stored as password hashes, not as plain text.<br><br>
    If you save and link a guest hero, you provide an email address. It is stored with your account and used for account recovery. When a guest hero is created, the game also records the IP address supplied by the web server.<br><br>
    The game uses a session cookie to keep you signed in. It is configured as HttpOnly and SameSite=Lax, and its lifetime depends on the service configuration.
</div>

<div class="tophead">
    <b>How Information Is Used</b><br><br>
    Information is used to operate the game, maintain and secure accounts, save progress, provide account recovery, support gameplay and communication features, and investigate abuse or technical problems. The game does not use account passwords for advertising.
</div>

<div class="tophead">
    <b>Analytics and External Services</b><br><br>
    The site may load Google Analytics if the game administrator has enabled it. When enabled, Google may receive information through its analytics service according to Google's own privacy terms. The site also loads fonts from Google Fonts; your browser connects to Google to retrieve those font files. These services may receive technical information such as your IP address and browser details.<br><br>
    The hosting provider may process technical request and error logs as part of operating the website. Their contents and retention depend on the hosting provider's configuration.
</div>

<div class="tophead">
    <b>Sharing and Retention</b><br><br>
    Account information is available to the game administration as needed to operate the service, provide support, and enforce the game rules. Information may also be processed by the hosting provider and the external services described above. We do not sell personal information.<br><br>
    Account and gameplay information is retained for as long as needed to provide and protect the service, meet operational needs, and comply with applicable requirements. Technical logs and analytics data may have separate retention periods set by the relevant provider.
</div>

<div class="tophead">
    <b>Your Choices and Questions</b><br><br>
    You can choose not to provide an email address, but you may then be unable to recover a guest hero if you lose access to it. For questions or requests about your account information, contact the game administration through the service's available contact channels. Requests may be subject to applicable law and account verification.
</div>

<div class="tophead">
    The game may update this policy as its features or practices change. Any updated version will be published on this page.
</div>
<?php
foot($uid);
?>
