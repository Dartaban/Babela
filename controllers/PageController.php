<?php

class Babela_PageController extends Omeka_Controller_AbstractActionController
{
    public function init()
    {
        // Get current language from SwitchLanguage
        $this->current_language = substr(getLanguageForOmekaSwitch(), 0, 2);
        $this->languages = explode("#", get_option('languages_options'));
        foreach ($this->languages as $i => $language) {
            $this->languages[$i] = substr($language, 0, 2);
        }
        // Remove default language from language list
        $locale = get_option('locale_lang_code');
        if (($key = array_search($locale, $this->languages)) !== false) {
            unset($this->languages[$key]);
        }
    }

    public function helpAction()
    {

    }

    public function translateMenusAction()
    {
        $form = $this->getMenusForm();
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {
                $texts = $form->getValues();
                // Sauvegarde form dans DB
                $db = get_db();
                $db->query("DELETE FROM `$db->TranslationRecords` WHERE record_type LIKE 'Menu'");
                foreach ($this->languages as $lang) {
                    foreach ($texts as $element_id => $translations) {
                        $query = "INSERT INTO `$db->TranslationRecords` VALUES (null, $element_id, 'Menu', 0, $element_id, 0, '" . substr($translations['lang_' . $element_id . '_' . $lang], 0, 2) . "', " . $db->quote($translations['ElementMenuTranslation_' . $element_id . '_' . $lang]) . ", 0)";
                        $db->query($query);
                    }
                }
            }
        }
        $this->view->form = $form;
    }

    public function getMenusForm()
    {
        $db = get_db();
        $menuTranslations = $db->query("SELECT * FROM `$db->TranslationRecords` WHERE record_type LIKE 'Menu'")->fetchAll();
        $translations = [];
        foreach ($menuTranslations as $x => $translationRecord) {
            $translations[$translationRecord['element_id']][$translationRecord['lang']] = $translationRecord['text'];
        }
        $form = new Zend_Form();
        $form->setName('BabelaTranslationMenuForm');

        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . public_nav_main()->setUlClass('auteur-onglets')->render());
        $elements = $dom->getElementsByTagName('li');
        $default_language = ucfirst(Locale::getDisplayLanguage(get_option('locale_lang_code'), Zend_Registry::get('Zend_Locale')));
        foreach ($elements as $i => $li) {
            $j = $i + 1;
            $text = trim($li->nodeValue);
            if (strpos($text, "\n")) {
                $text = substr($text, 0, strpos($text, "\n"));
            }
            $original = new Zend_Form_Element_Note('ElementMenu_' . $j);
            $original->setLabel($default_language . ' : ');
            $original->setValue("<h4>" . $text . "</h4>");
            $original->setBelongsto($j);
            $form->addElement($original);

            foreach ($this->languages as $lang) {
                $language = new Zend_Form_Element_Hidden('lang_' . $j . '_' . $lang . ' : ');
                $language->setValue($lang);
                $language->setBelongsto($j);
                $form->addElement($language);

                // Corps
                $textMenu = new Zend_Form_Element_Text('texte');
                $textMenu->setLabel(ucfirst(Locale::getDisplayLanguage($lang, $this->current_language)) . ' : ');
                $textMenu->setName('ElementMenuTranslation_' . $j . '_' . $lang);
                if (isset($translations[$j][$lang])) {
                    $textMenu->setValue($translations[$j][$lang]);
                }
                $textMenu->setBelongsto($j);
                $form->addElement($textMenu);
            }
        }
        unset($dom);

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save Translations');
        $submit->setValue('');
        $form->addElement($submit);

        $this->prettifyForm2($form);
        return $form;
    }

    public function listSimplePagesAction()
    {
        if (plugin_is_active('SimplePages')) {
            $db = get_db();
            $simplePages = $db->query("SELECT title, id FROM `$db->SimplePagesPage`")->fetchAll();
            $list = "<ul>";
            foreach ($simplePages as $i => $page) {
                $list .= "<li><a href='" . WEB_ROOT . "/admin/babela/simple-page/" . $page['id'] . "' target='_blank'>" . $page['title'] . "</a></li>";
            }
            $list .= "</ul>";

            $this->view->content = $list;
        }
    }

    public function listExhibitsPagesAction()
    {
        if (plugin_is_active('ExhibitBuilder')) {
            $db = get_db();
            $exhibits = $db->query("SELECT title, id FROM `$db->Exhibits`")->fetchAll();
            $list = "<ul>";
            foreach ($exhibits as $i => $exhibit) {
                $list .= "<li><a href='" . WEB_ROOT . "/admin/babela/exhibit/" . $exhibit['id'] . "' target='_blank'>" . $exhibit['title'] . "</a></li>";
                $exhibitPages = $db->getTable("ExhibitPage")->findBy(array('exhibit_id' => $exhibit['id'], 'sort_field' => 'order'));
                $list .= "<ul>";
                foreach ($exhibitPages as $ii => $exhibitPage) {
                    $list .= "<li><a href='" . WEB_ROOT . "/admin/babela/exhibit/page/" . $exhibitPage['id'] . "' target='_blank'>" . $exhibitPage['title'] . "</a></li>";
                }
                $list .= "</ul>";
            }
            $list .= "</ul>";

            $this->view->content = $list;
        }
    }

    public function translateTagsAction()
    {
        $form = $this->getTagsForm();
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {
                $texts = $form->getValues();
                /*                echo"<div style='float:right;text-align:right;'>";
                                Zend_Debug::dump($texts);
                                echo"</div>";
                                die();*/
                // Sauvegarde form dans DB
                $db = get_db();
                $db->query("DELETE FROM `$db->TranslationRecords` WHERE record_type LIKE 'Tag'");
                foreach ($this->languages as $lang) {
                    foreach ($texts as $element_id => $translations) {
                        if ($translations['TagTranslation_' . $element_id . '_' . $lang] != '') {
                            $query = "INSERT INTO `$db->TranslationRecords` VALUES (null, $element_id, 'Tag', 0, 0, 0, '$lang', " . $db->quote($translations['TagTranslation_' . $element_id . '_' . $lang]) . ", 0)";
                            $db->query($query);
                        }
                    }
                }
            }
        }
        $this->view->form = $form;
    }

    public function translateSimpleVocabAction()
    {
        $form = false;
        if (isset($params['module'])) {
            if ($params['module'] == 'simple-vocab') {
                $form = $this->getSimpleVocabForm();
            }
        }
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {
                $texts = $form->getValues();
                // Sauvegarde form dans DB
                $db = get_db();
                $db->query("DELETE FROM `$db->TranslationRecords` WHERE record_type LIKE 'SimpleVocab'");
                foreach ($this->languages as $lang) {
                    foreach ($texts as $element_id => $translations) {
//             Zend_Debug::dump($translations);
                        $query = "INSERT INTO `$db->TranslationRecords` VALUES (null, $element_id, 'SimpleVocab', 0, $element_id, 0, '" . $translations['lang_' . $element_id . '_' . $lang] . "', " . $db->quote($translations['ElementNameTranslation_' . $element_id . '_' . $lang]) . ", 0)";
                        $db->query($query);
                    }
                }
            }
        }
        if ($form) {
            $this->view->form = $form;
        }
    }

    public function translateSimplePageAction()
    {
        $id = $this->getParam('id');
        $form = $this->getSimplePageForm($id);
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {
                $texts = $form->getValues();
                // Sauvegarde form dans DB
                $db = get_db();
                $db->query("DELETE FROM `$db->TranslationRecords` WHERE record_type LIKE 'SimplePage%' AND record_id = " . $id);
                foreach ($texts as $fieldName => $translations) {
                    if (is_array($translations)) {
                        foreach ($translations as $lang => $field) {
                            $value = array_values($field);
                            $value = $db->quote($value[0]);
                            if ($value) {
                                if (array_key_exists("use_tiny_mce_" . $lang, $texts) && $texts["use_tiny_mce_" . $lang] == 1 && array_key_exists("text" . $lang, $field)) {
                                    $useHtml = 1;
                                } else {
                                    $useHtml = 0;
                                }

                                $query = "INSERT INTO `$db->TranslationRecords` VALUES (null, $id, 'SimplePage" . ucfirst($fieldName) . "', 0, 0, 0, '$lang', $value, $useHtml)";
                                $db->query($query);
                            }
                        }
                    }
                    $useHtml = 0;
                }
            }
        }
        // Retrieve orignal texts from DB
        $db = get_db();
        $original = $db->query("SELECT * FROM `$db->SimplePagesPage` WHERE id = " . $id)->fetchAll();
        $original = "<details><summary>Original texts</summary><div><em>Title</em> : " . $original[0]['title'] . "<br /><br /><em>Text</em> : " . $original[0]['text'] . "</div></details>";
        $this->view->form = $original . $form;
    }

    public function translateTermsAction()
    {
        $form = $this->getTranslationsForm();
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {
                if (isset($formData['translations'])) {
                    unset($formData['translations'], $formData['submit']);
                    $translations = base64_encode(serialize($formData));
                    set_option('babela_terms_translations', $translations);
                    $this->view->form = $form;
                    return true;
                }
            }
        }
        $this->view->form = $form;
    }

    public function translateExhibitAction()
    {
        $id = $this->getParam('id');
        $form = $this->getExhibitForm($id);
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {
                $texts = $form->getValues();
                // Sauvegarde form dans DB
                $db = get_db();
                $db->query("DELETE FROM `$db->TranslationRecords` WHERE record_type LIKE 'Exhibit%' AND record_id = " . $id);
                foreach ($texts as $fieldName => $translations) {
                    if (is_array($translations)) {
                        foreach ($translations as $lang => $field) {
                            $valueN = array_values($field);
                            $value = $db->quote($valueN[0]);
                            if ($valueN[0] != "") {
                                if ($fieldName == "description") {
                                    $useHtml = 1;
                                } else {
                                    $useHtml = 0;
                                }
                                $query = "INSERT INTO `$db->TranslationRecords` VALUES (null, $id, 'Exhibit" . ucfirst($fieldName) . "', 0, 0, 0, '$lang', $value, $useHtml)";
                                $db->query($query);
                            }
                        }
                    }
                    $useHtml = 0;
                }
            }
        }
        // Retrieve orignal texts from DB
        $db = get_db();
        $original = $db->query("SELECT * FROM `$db->Exhibits` WHERE id = " . $id)->fetchAll();
        $original = "<details><summary>Original texts</summary><div><em>Title</em> : " . $original[0]['title'] . "<br /><br /><em>Credits</em> : " . $original[0]['credits'] . "<br /><br /><em>Description</em> : " . $original[0]['description'] . "</div></details>";
        $this->view->form = $original . $form;
    }

    public function translateExhibitPageAction()
    {
        $id = $this->getParam('id');
        $form = $this->getExhibitPageForm($id);
        $linksPageBlocksTransLate = "<ul>";
        foreach ($this->languages as $lang) {
            $linksPageBlocksTransLate .= "<li><a href='$id/blocks/$lang'>Traduire blocks : " . Locale::getDisplayLanguage($lang, $this->current_language) . "</a></li>";
        }
        $linksPageBlocksTransLate .= "</ul>";

        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {
                $texts = $form->getValues();
                // Sauvegarde form dans DB
                $db = get_db();
                $db->query("DELETE FROM `$db->TranslationRecords` WHERE record_type LIKE 'PageExhibit%' AND record_id = " . $id);
                $useHtml = 0;
                foreach ($texts as $fieldName => $translations) {
                    if (is_array($translations)) {
                        foreach ($translations as $lang => $field) {
                            $value = array_values($field);
                            if ($value[0]) {
                                $value = $db->quote($value[0]);
                                $query = "INSERT INTO `$db->TranslationRecords` VALUES (null, $id, 'PageExhibit" . ucfirst($fieldName) . "', 0, 0, 0, '$lang', $value, $useHtml)";
                                $db->query($query);
                            }
                        }
                    }
                    $useHtml = 0;
                }
            }
        }

        // Retrieve orignal texts from DB
        $db = get_db();
        $original = $db->query("SELECT * FROM `$db->ExhibitPages` WHERE id = " . $id)->fetchAll();
        $original = "<details><summary>Original texts</summary><div><em>Title</em> : " . $original[0]['title'] . "<br /><br /><em>Short title</em> : " . $original[0]['short_title'] . "</div></details>";
        $this->view->form = $original . $form . $linksPageBlocksTransLate;
    }

    public function translateExhibitPageBlocksAction()
    {
        $id = $this->getParam('id');
        $lang = $this->getParam('lang');
        $form = $this->getExhibitPageBlocksForm($id, $lang);
        $linksPageBlocksTransLate = "<ul>";
        foreach ($this->languages as $langT) {
            $linksPageBlocksTransLate .= "<li><a href='$langT'>Traduire blocks : " . Locale::getDisplayLanguage($langT, $this->current_language) . "</a></li>";
        }
        $linksPageBlocksTransLate .= "</ul>";

        /*        if ($this->_request->isPost()) {
                    $formData = $this->_request->getPost();
                    if ($form->isValid($formData)) {
                        $texts = $form->getValues();
                        // Sauvegarde form dans DB
                        $db = get_db();
                        $db->query("DELETE FROM `$db->TranslationRecords` WHERE record_type LIKE 'PageExhibit%' AND record_id = " . $id);
                        $useHtml = 0;
                        foreach ($texts as $fieldName => $translations) {
                            if (is_array($translations)) {
                                foreach ($translations as $lang => $field) {
                                    $value = array_values($field);
                                    if ($value[0]) {
                                        $value = $db->quote($value[0]);
                                        $query = "INSERT INTO `$db->TranslationRecords` VALUES (null, $id, 'PageExhibit" . ucfirst($fieldName) . "', 0, 0, 0, '$lang', $value, $useHtml)";
                                        $db->query($query);
                                    }
                                }
                            }
                            $useHtml = 0;
                        }
                    }
                }

                // Retrieve orignal texts from DB
                $db = get_db();
                $original = $db->query("SELECT * FROM `$db->ExhibitPages` WHERE id = " . $id)->fetchAll();
                $original = "<details><summary>Original texts</summary><div><em>Title</em> : " . $original[0]['title'] . "<br /><br /><em>Short title</em> : " . $original[0]['short_title'] . "</div></details>";*/
        $this->view->form = $form . $linksPageBlocksTransLate;
    }

    public function getExhibitPageBlocksForm($idPage, $lang)
    {
        $db = get_db();
        // Retrieve original blocks for this page from DB
        $originals = $db->query("SELECT * FROM `$db->ExhibitPageBlocks` WHERE page_id = $idPage ORDER BY 'order' ASC")->fetchAll();
        $form = new Zend_Form();

        foreach ($originals as $i => $original) {
            $layout = $original['layout'];
            $order = $original['order'];
            $text = $original['text'];
            $form->setName('ExhibitPageBlocksForm'.$i);
            // Original
            $originalText = new Zend_Form_Element_Note('OriginalText_' . $i);
            $originalText->setValue($text);
            $originalText->setLabel("Block Original $order ($layout)");
            $originalText->setBelongsto($i);
            $form->addElement($originalText);
        }
        return $form;
    }

    public function getSimpleVocabForm()
    {
        $db = get_db();
        // Retrieve translations for this page type from DB
        $translatedTerms = $db->query("SELECT t.element_id id, t.terms terms, e.name name, tr.text trans, tr.lang lang
		                     FROM `$db->SimpleVocabTerms` t
		                      LEFT JOIN `$db->Elements` e ON e.id = t.element_id
		                      LEFT JOIN `$db->TranslationRecords` tr ON tr.element_id = t.element_id")->fetchAll();

        $form = new Zend_Form();
        $form->setName('BabelaTranslationSVForm');
        // TODO : Synchro $terms / form
        $terms = [];
        foreach ($translatedTerms as $i => $term) {
            $terms[$term['id']]['name'] = $term['name'];
            $terms[$term['id']]['terms'] = $term['terms'];
            $terms[$term['id']][$term['lang']] = $term['trans'];
        }
// 		Zend_Debug::dump($terms);
        foreach ($terms as $id => $term) {
            // Element
            $original = new Zend_Form_Element_Note('ElementName_' . $id);
            $original->setValue("<h3>" . __($term['name']) . "</h3>");
            $form->addElement($original);
            $default_language = ucfirst(Locale::getDisplayLanguage(get_option('locale_lang_code'), Zend_Registry::get('Zend_Locale')));
            foreach ($this->languages as $lang) {
                $language = new Zend_Form_Element_Hidden('lang_' . $id . '_' . $lang);
                $language->setValue($lang);
                $language->setBelongsto($id);
                $form->addElement($language);

                // Original
                $original = new Zend_Form_Element_Note('OriginalTerm_' . $id);
                $original->setValue(nl2br($term['terms']) . '<br /><br />');
                $original->setLabel($default_language);
                $original->setBelongsto($id);
                $form->addElement($original);

                // Corps
                $lines = substr_count($term['terms'], PHP_EOL) + 1;
                $textTerm = new Zend_Form_Element_Textarea('texte');
                $textTerm->setAttrib('rows', $lines);
                $textTerm->setLabel(ucfirst(Locale::getDisplayLanguage($lang, $this->current_language)));
                $textTerm->setName('ElementNameTranslation_' . $id . '_' . $lang);
                if (isset($term[$lang]) && $term[$lang] <> '') {
                    $textTerm->setValue($term[$lang]);
                }
                $textTerm->setBelongsto($id);
                $form->addElement($textTerm);
            }
        }

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save Translation');
        $submit->setValue('');
        $form->addElement($submit);

        $form = $this->prettifyForm($form);
        return $form;
    }

    // ExhibitPageBlock.php

    public function getTagsForm()
    {
        $db = get_db();
        // Retrieve translations for tags from DB
        $translations = $db->query("SELECT * FROM `$db->TranslationRecords` WHERE record_type LIKE 'Tag'")->fetchAll();

        $form = new Zend_Form();
        $form->setName('BabelaTranslationSVForm');
        if ($translations) {
            $tagsTranslated = [];
            foreach ($translations as $index => $tagTranslated) {
                $tagsTranslated[$tagTranslated['record_id'] . "-" . $tagTranslated['lang']]['text'] = $tagTranslated['text'];
                $tagsTranslated[$tagTranslated['record_id'] . "-" . $tagTranslated['lang']]['record_id'] = $tagTranslated['record_id'];
                $tagsTranslated[$tagTranslated['record_id'] . "-" . $tagTranslated['lang']]['lang'] = $tagTranslated['lang'];
            }
        }

        $originalTags = get_records('Tag', array('sort_field' => 'name', 'sort_dir' => 'a'), 1000000);
        /*        echo"<div style='float:right;text-align:right;width:100%;'>";
                Zend_Debug::dump($tagsTranslated);
                Zend_Debug::dump($originalTags);
                echo"</div>";*/
        foreach ($originalTags as $tag) {


            foreach ($this->languages as $lang) {
                $id = $tag->id;
                $name = $tag->name;
                $original = new Zend_Form_Element_Note('OriginalTag_' . $tag->id);

                $default_language = ucfirst(Locale::getDisplayLanguage(get_option('locale_lang_code'), Zend_Registry::get('Zend_Locale')));
                $original->setLabel($default_language);
                $original->setValue("<b>" . __($name) . "</b>");
                $form->addElement($original);

                $textTag = new Zend_Form_Element_Textarea('texte');
                $textTag->setAttrib('rows', 1);
                $textTag->setLabel(ucfirst(Locale::getDisplayLanguage($lang, $this->current_language)));
                $textTag->setName('TagTranslation_' . $id . '_' . $lang);
                /*                echo"<div style='float:right;text-align:right;width:100%;'>";
                                Zend_Debug::dump('xxxx');
                                echo"</div>";
                                echo"<div style='float:right;text-align:right;width:100%;'>";
                                Zend_Debug::dump($tagsTranslated[171]);
                                echo"</div>";*/
                if ($tagsTranslated[$id . "-" . $lang]['text'] != '') {
                    /*                    echo"<div style='float:right;text-align:right;width:100%;'>";
                                        Zend_Debug::dump('YYYYEEEAAAHHHH');
                                        echo"</div>";*/
                    $textTag->setValue($tagsTranslated[$id . "-" . $lang]['text']);
                }
                $textTag->setBelongsto($id);
                $form->addElement($textTag);
            }
        }

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save Translation');
        $submit->setValue('');
        $form->addElement($submit);

        $form = $this->prettifyForm($form);
        return $form;
    }

    public function getSimplePageForm($id)
    {
        $db = get_db();
        // Retrieve translations for this page type from DB
        $translations = $db->query("SELECT * FROM `$db->TranslationRecords` WHERE record_type LIKE 'SimplePage%' AND record_id = " . $id)->fetchAll();
        if ($translations) {
            $values = array();
            foreach ($translations as $index => $texts) {
                $fieldName = substr($texts['record_type'], 10);
                $values[$fieldName][$texts['lang']]['text'] = $texts['text'];
                $values[$fieldName][$texts['lang']]['html'] = $texts['html'];
            }
        }

        $form = new Zend_Form();
        $form->setName('BabelaTranslationSSForm');

        foreach ($this->languages as $lang) {
            $titleName = "title[$lang]";
            $textName = "text[$lang]";

            // Titre
            $titleSS = new Zend_Form_Element_Text('title');
            $titleSS->setLabel('Title (' . Locale::getDisplayLanguage($lang, $this->current_language) . ')');
            $titleSS->setName($titleName);
            if (isset($values['Title'][$lang]['text'])) {
                $titleSS->setValue($values['Title'][$lang]['text']);
            }
            $titleSS->setBelongsTo($titleName);
            $form->addElement($titleSS);
            $checked = $values[$fieldName][$lang]['html'];
            if ((int)$checked > 0) {
                $checked = (int)$checked;
            } else {
                $checked = false;
            }
            $html = $form->createElement(
                'checkbox', 'use_tiny_mce_' . $lang,
                array(
                    'id' => 'babela-use-tiny-mce-' . $lang,
                    'class' => 'babela-use-tiny-mce',
                    'checked' => $checked,
                    'values' => array(1, 0),
                    'label' => __('Use HTML editor?'),
                )
            );
            $form->addElement($html);

            // Corps
            $textSS = new Zend_Form_Element_Textarea('texte');
            $textSS->setLabel('Text (' . Locale::getDisplayLanguage($lang, $this->current_language) . ')');
            $textSS->setName($textName);
            if (isset($values['Text'][$lang]['text'])) {
                $textSS->setValue($values['Text'][$lang]['text']);
            }
            $textSS->setBelongsTo($textName);
            $textSS->setAttrib('class', 'babela-use-html');
            $form->addElement($textSS);
        }

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save Translation');
        $form->addElement($submit);

        return $form;
    }

    public function getTranslationsForm()
    {
        include_once(PLUGIN_DIR . '/Babela/themeStrings.php');
        $form = new Zend_Form();
        $form->setName('BabelaTranslationsForm');

        $t = new Zend_Form_Element_Hidden('translations');
        $t->setValue(1);
        $form->addElement($t);
        $translations = get_option('babela_terms_translations');
        $translations = unserialize(base64_decode($translations));
        $categoryNumber = 0;
        foreach ($strings as $title => $category) {
            $categoryTitle = new Zend_Form_Element_Note('categoryTitle_' . $title);
            $categoryTitle->setValue("<h3>$title</h3>");
            $categoryTitle->setBelongsTo($title);
            $form->addElement($categoryTitle);
            $languages = array_values($this->languages);
            $default_language = ucfirst(Locale::getDisplayLanguage(get_option('locale_lang_code'), Zend_Registry::get('Zend_Locale')));
            $languages[] = get_option('locale_lang_code');
            foreach ($category as $j => $string) {
                foreach ($languages as $x => $lang) {
                    $current_language = ucfirst(Locale::getDisplayLanguage($lang, Zend_Registry::get('Zend_Locale')));
                    if ($lang == get_option('locale_lang_code')) {
                        $language = new Zend_Form_Element_Hidden('string_' . $categoryNumber . '_' . $j . '_' . $lang);
                    } else {
                        $language = new Zend_Form_Element_Text('string_' . $categoryNumber . '_' . $j . '_' . $lang);
                        $language->setLabel("Du " . $default_language . ' : "' . $string . '", traduire en ' . $current_language . ' => ');
                    }
                    if (isset ($translations[$title]['string_' . $categoryNumber . '_' . $j . '_' . $lang])) {
                        $language->setValue(trim($translations[$title]['string_' . $categoryNumber . '_' . $j . '_' . $lang]));
                    } else {
                        $language->setValue(trim($string));
                    }
                    $language->setBelongsto($title);
                    $form->addElement($language);
                }
            }
            $categoryNumber++;
        }

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save Terms Translations');
        $form->addElement($submit);
        $form = $this->prettifyForm($form);
        $elements = $form->getElements();
        foreach ($elements as $elem) {
            if ($elem instanceof Zend_Form_Element_Hidden) {
                $elem->setDecorators(array('ViewHelper'));
            }
        }
        return $form;
    }

    public function getExhibitForm($id)
    {
        $db = get_db();
        // Retrieve translations for this page type from DB
        $translations = $db->query("SELECT * FROM `$db->TranslationRecords` WHERE record_type LIKE 'Exhibit%' AND record_id = " . $id)->fetchAll();
        if ($translations) {
            $values = array();
            foreach ($translations as $index => $texts) {
                $fieldName = substr($texts['record_type'], 7);
                $values[$fieldName][$texts['lang']] = $texts['text'];
                $values[$fieldName]['html'] = $texts['html'];
            }
        }

        $form = new Zend_Form();
        $form->setName('BabelaTranslationSSForm');
        foreach ($this->languages as $lang) {
            $titleName = "title[$lang]";
            $creditsName = "credits[$lang]";
            $descriptionName = "description[$lang]";

            // Titre
            $titleSS = new Zend_Form_Element_Text('title');
            $titleSS->setLabel('Title (' . Locale::getDisplayLanguage($lang, $this->current_language) . ')');
            $titleSS->setName($titleName);
            if (isset($values['Title'][$lang])) {
                $titleSS->setValue($values['Title'][$lang]);
            }
            $titleSS->setBelongsTo($titleName);
            $form->addElement($titleSS);

            // Credits
            $creditsSS = new Zend_Form_Element_Text('credits');
            $creditsSS->setLabel('Credits (' . Locale::getDisplayLanguage($lang, $this->current_language) . ')');
            $creditsSS->setName($creditsName);
            if (isset($values['Credits'][$lang])) {
                $creditsSS->setValue($values['Credits'][$lang]);
            }
            $creditsSS->setBelongsTo($creditsName);
            $form->addElement($creditsSS);

            $html = $form->createElement(
                'hidden', 'use_tiny_mce_' . $lang,
                array(
                    'id' => 'babela-use-tiny-mce-' . $lang,
                    'class' => 'babela-use-tiny-mce',
                    'values' => 1,
                )
            );
            $form->addElement($html);

            // Corps
            $descriptionSS = new Zend_Form_Element_Textarea('description');
            $descriptionSS->setLabel('Description (' . Locale::getDisplayLanguage($lang, $this->current_language) . ')');
            $descriptionSS->setName($descriptionName);
            if (isset($values['Description'][$lang])) {
                $descriptionSS->setValue($values['Description'][$lang]);
            }
            $descriptionSS->setBelongsTo($descriptionName);
            $descriptionSS->setAttrib('class', 'babela-use-html');
            $form->addElement($descriptionSS);
        }

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save Translation');
        $form->addElement($submit);

        return $form;
    }

    public function getExhibitPageForm($id)
    {

        $db = get_db();
        // Retrieve translations for this page type from DB
        $translations = $db->query("SELECT * FROM `$db->TranslationRecords` WHERE record_type LIKE 'PageExhibit%' AND record_id = " . $id)->fetchAll();
        if ($translations) {
            $values = array();
            foreach ($translations as $index => $texts) {
                $fieldName = substr($texts['record_type'], 11);
                $values[$fieldName][$texts['lang']] = $texts['text'];
                $values[$fieldName]['html'] = $texts['html'];
            }
        }

        $form = new Zend_Form();
        $form->setName('BabelaTranslationSSForm');
        foreach ($this->languages as $lang) {
            $titleName = "title[$lang]";
            $shortTitleName = "menu_title[$lang]";
            $linkTranslatePageBlocksName = "note_link_translate_page_blocks[$lang]";

            // Title
            $titleSS = new Zend_Form_Element_Text('title');
            $titleSS->setLabel('Title (' . Locale::getDisplayLanguage($lang, $this->current_language) . ')');
            $titleSS->setName($titleName);
            if (isset($values['Title'][$lang])) {
                $titleSS->setValue($values['Title'][$lang]);
            }
            $titleSS->setBelongsTo($titleName);
            $form->addElement($titleSS);

            // Short Title
            $shorttitleSS = new Zend_Form_Element_Text('menu_title');
            $shorttitleSS->setLabel('Short Title (' . Locale::getDisplayLanguage($lang, $this->current_language) . ')');
            $shorttitleSS->setName($shortTitleName);
            if (isset($values['Menu_title'][$lang])) {
                $shorttitleSS->setValue($values['Menu_title'][$lang]);
            }
            $shorttitleSS->setBelongsTo($shortTitleName);
            $form->addElement($shorttitleSS);

            // Translate content link
            //$linkTranslatePageBlocks = new Zend_Form_Element_Note('note_link_translate_page_blocks');
            //$linkTranslatePageBlocks->setValue("<p><a href='/blocks/$lang'>Traduire blocks</a>".'(' . Locale::getDisplayLanguage($lang, $this->current_language) . ')'."</p>");
            //$linkTranslatePageBlocks->setBelongsTo($linkTranslatePageBlocksName);
            //$form->addElement($linkTranslatePageBlocks);
        }

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save Translation');
        $form->addElement($submit);

        return $form;

    }

    private function prettifyForm2($form)
    {
        // Prettify form
        $blocks = $form->getElements();

        foreach ($blocks as $elem) {
            if ($elem instanceof Zend_Form_Element_Hidden) {
                $elem->removeDecorator('label')->removeDecorator('HtmlTag');
            }
        }

        // Fieldset pour les blocs
        $displayGroups = [];
        $currentDisplayGroup = '';
        foreach ($form->getElements() as $name => $block) {
            $displayGroup = $block->getBelongsTo();
            if ($displayGroup <> $currentDisplayGroup) {
                $currentDisplayGroup = $displayGroup;
            }
            $displayGroups[$currentDisplayGroup][] = $name;
        }
        foreach ($displayGroups as $block => $displayGroup) {
            if ($block) {
                $form->addDisplayGroup($displayGroup, $block);
                $form->getDisplayGroup($block)->removeDecorator('DtDdWrapper');
            } else {
                $form->addDisplayGroup($displayGroup, 'general');
            }
        }
        $form->setDisplayGroupDecorators(array(
            'FormElements',
            'Fieldset',
            array('Fieldset', array('class' => 'uitemplates-fieldset'))
        ));
        $form->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'div', 'class' => 'uitemplates-form')),
            'Form'
        ));
        $form->setElementDecorators(array(
                'ViewHelper',
                'Errors',
                array('Description', array('tag' => 'p', 'class' => 'description')),
                array('HtmlTag', array('class' => 'form-div')),
                array('Label', array('class' => 'form-label'))
            )
        );
        $form->setElementDecorators(array(
                'ViewHelper',
                'Label',
                new Zend_Form_Decorator_HtmlTag(array('tag' => 'div', 'class' => 'elem-wrapper'))
            )
        );
        return $form;
    }

    private function prettifyForm($form)
    {
        // Prettify form
        $form->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table')),
            'Form'
        ));
        $form->setElementDecorators(array(
            'ViewHelper',
            'Errors',
            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
            array('Label', array('tag' => 'td', 'style' => 'text-align:right;float:right;')),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
        ));
        $blocks = $form->getElements();
        foreach ($blocks as $elem) {
            if ($elem instanceof Zend_Form_Element_Hidden) {
                $elem->removeDecorator('label')->removeDecorator('HtmlTag');
            }
        }
        return $form;
    }

}
