<?php
echo head(array('title' => "Babela"));
echo flash();
?>
<nav id="section-nav" class="navigation vertical">
    <ul class="dropdown">
        <li class='active'>
            <a href='<?php echo WEB_ROOT; ?>/admin/babela/help'>Fonctionnement du plugin</a>
        </li>
        <li>
            <a href='<?php echo WEB_ROOT; ?>/admin/babela/menus'>Menus</a>
        </li>
        <li>
            <a href='<?php echo WEB_ROOT; ?>/admin/babela/simple-vocab'>Simple Vocab</a>
        </li>
        <li>
            <a href='<?php echo WEB_ROOT; ?>/admin/babela/list-simple-pages'>Simple Pages</a>
        </li>
        <li>
            <a href='<?php echo WEB_ROOT; ?>/admin/babela/terms'>Termes</a>
        </li>

        <!--
            <li>
                <a href='<?php echo WEB_ROOT; ?>/admin/babela/list-exhibits-pages'>Expositions</a>
            </li>
            -->
        <!--
        <li>
            <a href='<?php echo WEB_ROOT; ?>/admin/babela/tags'>Tags</a>
        </li>
        -->
    </ul>
</nav>
<h2>Fonctionnement du plugin</h2>

<div>
    <p>Texte de la documentation à saisir ici.</p>
    <p>Voir le readme du plugin ou se rendre sur cette <a href="https://github.com/Dartaban/Babela/tree/master" target="_blank">page</a></p>

</div>
<?php

echo foot();

?>

