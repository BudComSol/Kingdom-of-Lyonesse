<fieldset style="border: 0; padding: 0; margin: 1em 0;">
    <legend><b>Choose your faction</b></legend>
    <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 12px;">
        <?php foreach (factions() as $id => $faction) { ?>
            <label style="display: block; width: 140px; padding: 8px; border: 1px solid #a68b45; border-radius: 8px; cursor: pointer;">
                <input type="radio" name="faction" value="<?php echo $id; ?>" required <?php if ($selectedFaction === $id) echo 'checked'; ?>>
                <b><?php echo $faction['name']; ?></b><br>
                <img src="/images/arena/<?php echo $id; ?>.png" width="120" height="170" style="object-fit: contain; max-width: 100%;" alt="<?php echo $faction['name']; ?>"><br>
                <small>
                    <?php
                    $bonuses = [];
                    foreach (['power' => 'Power', 'block' => 'Defense', 'health' => 'Health'] as $stat => $label) {
                        if ($faction[$stat]) $bonuses[] = '+' . $faction[$stat] . '% ' . $label;
                    }
                    echo implode(', ', $bonuses);
                    ?>
                </small>
            </label>
        <?php } ?>
    </div>
</fieldset>
