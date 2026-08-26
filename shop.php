<?php
$location = true;
require_once __DIR__ . "/core/func.php";

$loc = number($_GET['loc'] ?? 0);
if ($loc >= 1 && $loc <= 5) {
    require_once __DIR__ . "/templates/shop" . $loc . ".php";
    exit();
}

head("Market Square & Merchants", $uid);
?>

<div class="tophead2">
    <div class="title cntr"><span class="color">Market Square Merchants</span></div><br>
    <div class="main_menu_section">
        <a class="main_menu_button" href="/shop.php?loc=1">
            <div class="main_menu_button_image active">
                <img src="/images/shop1.png" width="25px" height="25px" alt="Luther">
            </div>
            <div class="main_menu_button_content">
                <span class="lime">Luther (Equipment Merchant)</span><br>
                <small>Hey friend! Come inspect the finest weapons and armor in the kingdom!</small>
            </div>
        </a>
        <a class="main_menu_button" href="/shop.php?loc=2">
            <div class="main_menu_button_image">
                <img src="/images/shop2.png" width="25px" height="25px" alt="Edward">
            </div>
            <div class="main_menu_button_content">
                <span class="lime">Edward (Blacksmith)</span><br>
                <small>Looking to forge and strengthen your weapons and armor? Step right in!</small>
            </div>
        </a>
        <a class="main_menu_button" href="/shop.php?loc=3">
            <div class="main_menu_button_image">
                <img src="/images/shop3.png" width="25px" height="25px" alt="Anna">
            </div>
            <div class="main_menu_button_content">
                <span class="lime">Anna (Runesmith)</span><br>
                <small>Ancient runes will grant unmatched magical properties to your equipment!</small>
            </div>
        </a>
    </div>

    <div class="main_menu_section">
        <a class="main_menu_button" href="/shop.php?loc=4">
            <div class="main_menu_button_image active">
                <img src="/images/shop4.png" width="25px" height="25px" alt="Richard">
            </div>
            <div class="main_menu_button_content">
                <span class="lime">Richard (Master Trainer)</span><br>
                <small>Without combat skills, you won't last long! Let me know when you are ready to train!</small>
            </div>
        </a>
        <a class="main_menu_button" href="/shop.php?loc=5">
            <div class="main_menu_button_image">
                <img src="/images/shop5.png" width="25px" height="25px" alt="Elizabeth">
            </div>
            <div class="main_menu_button_content">
                <span class="lime">Elizabeth (Resource Merchant)</span><br>
                <small>Don't want to risk your life in the Dark Forest? Browse my crafting materials!</small>
            </div>
        </a>
    </div>

    <br>
    <div class="title cntr"><span class="color">Services & Trading</span></div>
    <br>
    <div class="main_menu_section">
        <a class="main_menu_button" href="/premium.php">
            <div class="main_menu_button_image active">
                <img src="/images/vip.png" width="20px" height="20px" alt="VIP">
            </div>
            <div class="main_menu_button_content">Premium VIP Account</div>
        </a>
        <a class="main_menu_button" href="/gold.php">
            <div class="main_menu_button_image">
                <img src="/images/gold.png" width="20px" height="20px" alt="Exchange">
            </div>
            <div class="main_menu_button_content">Currency Exchange</div>
        </a>
    </div>
</div>

<?php
foot($uid);
?>