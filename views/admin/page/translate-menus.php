<?php
queue_js_file('vendor/tinymce/tinymce.min');
$head = array('bodyclass' => 'babel primary', 
              'title' => __('Babel | Translate Menus'));
echo head($head);
echo flash(); 
?>
<nav id="section-nav" class="navigation vertical">
<ul class="dropdown">
  <li>
    <a href='<?php echo WEB_ROOT; ?>/admin/babel/help'>Fonctionnement du plugin</a>
  </li>
  <li class='active'>
    <a href='<?php echo WEB_ROOT; ?>/admin/babel/menus'>Menus</a>
  </li>
  <li>
    <a href='<?php echo WEB_ROOT; ?>/admin/babel/simple-vocab'>Simple Vocab</a>
  </li>
  <li>
    <a href='<?php echo WEB_ROOT; ?>/admin/babel/list-simple-pages'>Simple Pages</a>
  </li>
  <li>
    <a href='<?php echo WEB_ROOT; ?>/admin/babel/terms'>Termes</a>  
  </li>
</ul>
</nav>
<h2>Saisissez les traductions des éléments de menus</h2>
 
<?php
 echo $form;
 echo foot(); 
?>