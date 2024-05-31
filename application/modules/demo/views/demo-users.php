<div>
    <strong><?=_lang("Demo Users")?></strong>

<?php foreach ($result as $user): ?>
    <div><a href="#"><?=$user['name']?></a></div>
<?php endforeach; ?>

</div>
