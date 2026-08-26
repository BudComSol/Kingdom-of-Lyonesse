<script type="text/javascript">
function tag(id, html) {
    var e = document.getElementById(id);
    if (e != null) {
        e.value += ' ' + html + ' ';
        e.focus();
    }
}
function showSmilies() {
    var e = document.getElementById('smiles');
    if (e != null) {
        e.style.display = (e.style.display == 'block') ? 'none' : 'block';
    }
}
</script>
<div class="smile" id="smiles" style="display: none;">
    <div class="hrs"></div>
    <small>
        <img src="/images/ruby.png" width="17px" height="17px" alt="Ruby"> Rubies: <?php echo $users['gold'] ?? 0; ?> <br>
        <img src="/images/gold.png" width="17px" height="17px" alt="Coins"> Coins: <?php echo number_format((int)($users['silver'] ?? 0)); ?><br>
        <img src="/images/metal.png" width="17px" height="17px" alt="Iron"> Iron: <?php echo $users['metal'] ?? 0; ?><br>
        <img src="/images/tkan.png" width="17px" height="17px" alt="Cloth"> Cloth: <?php echo $users['tkan'] ?? 0; ?><br>
        <img src="/images/kamen.png" width="17px" height="17px" alt="Stone"> Stone: <?php echo $users['kamen'] ?? 0; ?><br>
        <img src="/images/swit.png" width="17px" height="17px" alt="Scrolls"> Scrolls: <?php echo $users['swit'] ?? 0; ?><br>
        <img src="/images/book.png" width="17px" height="17px" alt="Books"> Skill Books: <?php echo $users['book'] ?? 0; ?><br>
        <img src="/images/skills.png" width="17px" height="17px" alt="Skill Points"> Skill Points: <?php echo $users['skills'] ?? 0; ?><br>
        <img src="/images/tour.png" width="17px" height="17px" alt="Trophies"> Trophies: <?php echo $users['tour'] ?? 0; ?><br>
    </small>
</div>