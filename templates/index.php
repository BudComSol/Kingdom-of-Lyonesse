<div class="tophead2">
    <div class="mb25ad">
        <a class="tower-preview-btn edge-offset" id="tower" href="/arena.php">
            <div class="tower-preview-btn__icon icon-tower2">
                <div class="icon-tower2__content"></div>
            </div>
            <div class="tower-preview-btn__title button-2019 button-2019_tower">
                <div class="button-2019__content">Arena</div>
            </div>
        </a>
    </div>

    <a class="tower-preview-btn2 edge-offset" id="tower2" href="/tavern.php">
        <div class="tower-preview-btn__icon icon-tower">
            <div class="icon-tower__content"></div>
        </div>
        <div class="tower-preview-btn__title button-2019 button-2019_tower">
            <div class="button-2019__content">Tavern</div>
        </div>
    </a>
</div>

<br>

<div class="tophead2">
    <div class="main_menu_section">
        <a class="main_menu_button" href="/lec.php">
            <div class="main_menu_button_image active">
                <img src="/images/fight1.png" width="20px" height="20px" alt="">
            </div>
            <div class="main_menu_button_content">
                Dark Forest
                <?php if (($user['lec'] ?? 0) >= 1) { echo ' <font color="lime">(+)</font>'; } ?>
            </div>
        </a>

        <a class="main_menu_button" href="/task.php">
            <div class="main_menu_button_image">
                <img src="/images/task.png" width="20px" height="20px" alt="">
            </div>
            <div class="main_menu_button_content">Daily Quests</div>
        </a>

        <a class="main_menu_button" href="/tour.php">
            <div class="main_menu_button_image">
                <img src="/images/tour.png" width="20px" height="20px" alt="">
            </div>
            <div class="main_menu_button_content">Royal Tournament</div>
        </a>
    </div>

    <div class="main_menu_section">
        <a class="main_menu_button" href="/shop.php">
            <div class="main_menu_button_image active">
                <img src="/images/shop.png" width="20px" height="20px" alt="">
            </div>
            <div class="main_menu_button_content">Market Square</div>
        </a>

        <a class="main_menu_button" href="/top.php">
            <div class="main_menu_button_image">
                <img src="/images/top.png" width="20px" height="20px" alt="">
            </div>
            <div class="main_menu_button_content">Hall of Fame</div>
        </a>

        <a class="main_menu_button" href="/gold.php">
            <div class="main_menu_button_image">
                <img src="/images/ruby.png" width="20px" height="20px" alt="">
            </div>
            <div class="main_menu_button_content">Ruby Exchange</div>
        </a>
    </div>
</div>
