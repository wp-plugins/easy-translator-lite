<?php
/*
Plugin Name: Easy Translator Lite
Plugin URI: http://www.thulasidas.com/ezTrans
Description: A plugin to translate other plugins (Yes, any other plugin) Access it by clicking <a href="tools.php?page=easy-translator-lite/easy-translator-lite.php">Tools &rarr; Easy Translator Lite</a>.
Version: 2.01
Author: Manoj Thulasidas
Author URI: http://www.thulasidas.com
*/

/*
Copyright (C) 2008 www.thulasidas.com

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

define('minmatch', 89) ;

if (!class_exists("ezTran") && !class_exists("PO")) {
  class PO { // an id-str pair with attributes
    var $num, $id, $str, $tranId, $tranVal, $keyId, $keyVal ;

    function PO($id, $str) {
      $this->id = (string) $id ;
      $this->str = (string) $str ;
      $this->tranVal = minmatch ;
      $this->keyVal = minmatch ;
    }

    // Returns a properly escaped string
    function decorate($str, $esc) {
      $str = stripslashes($str) ;
      $str = addcslashes($str, $esc) ;
      return $str ;
    }

    // Returns a text-area string of the Id
    function textId() {
      $ht = round(strlen($this->id)/52 + 1) * 25 ;
      $col = 'background-color:#f5f5f5;' ;
      if ($this->keyVal > minmatch+1) {
        $col = "background-color:#ffc;border: solid 1px #f00" ;
        $tit = 'onmouseover = "Tip(\'Another similar string: ' .
          htmlspecialchars('<br /><em><b>' . $this->decorate($this->keyId, "\n") .
                           '</b></em><br /> ', ENT_QUOTES) .
          'exists. Please alert the author.\',WIDTH, 300)" ' .
          'onmouseout="UnTip()"';
      }
      $s = '<textarea cols="50" rows="15" name="k' . $this->num .
        '" style="width: 45%;height:' . $ht . 'px;' . $col . '" ' .
        $tit . ' readonly="readonly">';
      $s .= htmlspecialchars($this->id, ENT_QUOTES) ;
      $s .= '</textarea>&nbsp;&nbsp;' ;
      return $s ;
    }

    function textStr() {
      $ht = round(strlen($this->id)/52 + 1) * 25 ;
      if ($this->tranVal > minmatch+1){
        $col = "background-color:#fdd;border: solid 1px #f00" ;
        $tit = 'onmouseover = "Tip(\'Using the translation for a similar string: ' .
          htmlspecialchars('<br /><em><b>' . $this->decorate($this->tranId, "\n") .
                           '</b></em><br />', ENT_QUOTES) .
          'Please check carefully.\',WIDTH, 300)" ' .
          'onmouseout="UnTip()"';
      }
      $s =  '<textarea cols="50" rows="15" name="' . $this->num .
        '" style="width: 45%;height:' . $ht . 'px;' . $col. '" ' .
        $tit . '>';
      $s .=  htmlspecialchars($this->str, ENT_QUOTES) ;
      $s .= '</textarea><br />' ;
      return $s ;
    }
  }

  class ezTran {
    var $status, $error;
    function ezTran()
    {
      session_start() ;
      $this->status = '' ;
      $this->error = '' ;

      if ($_POST['ezt-savePot']) {
        $file = $_POST['potFile'] ;
        $str = $_POST['potStr'] ;
        header('Content-Disposition: attachment; filename="' . $file .'"');
        header("Content-Transfer-Encoding: ascii");
        header('Expires: 0');
        header('Pragma: no-cache');
        ob_start() ;
        print stripslashes(htmlspecialchars_decode($str, ENT_QUOTES)) ;
        ob_end_flush() ;
        $this->status = '<div class="updated">Pot file: ' . $file . ' was saved.</div> ' ;
        exit(0) ;
      }
      if ($_POST['ezt-clear']) {
        $this->status =
          '<div class="updated">Reloaded the translations from PHP files and MO.</div> ' ;
        unset($_SESSION['ezt-POs']) ;
        $_POST['ezt-loadmo'] = 'Load MO' ;
        // session_destroy() ;
      }
      if ($_POST['ezt-mailPot']) {
        echo '<div style="background-color:#cff;padding:5px;border: solid 1px;margin-top:10px;">';
        $this->status = '<div class="updated">In the <a href="http://buy.thulasidas.com/easy-translator">Pro Version</a>, the Pot file: ' . $file . ' would have been  sent to ' . $author . ' (' . $authormail . '). In this Lite version, please download the PO file and email it using your mail client.</div> ' ;
        echo '</div>' ;
      }
    }

    // Returns a properly escaped string
    function decorate($str, $esc) {
      $str = stripslashes($str) ;
      $str = addcslashes($str, $esc) ;
      return $str ;
    }

    // Return the contents of all PHP files in the dir specified
    function getFileContents($dir="") {
      if ($dir == "") $dir = dirname(__FILE__) ;
      $files = glob($dir . '/*.php') ;
      $page = "" ;
      foreach ($files as $f) {
        $page .= file_get_contents($f, FILE_IGNORE_NEW_LINES) ;
      }
      return $page ;
    }

    // Percentage Levenshtein distance
    function levDist(&$s1, &$s2) {
      similar_text($s1, $s2, $p) ;
      return round($p) ;
    }

    // Get the closest existing translation keys, and the recursivley closest in the
    // key set
    function getClose(&$mo, &$POs){
      foreach ($POs as $n => $po){
        $s1 = $po->id ;
        $l1 = strlen($s1);
        if (strlen($po->str) == 0) {
          if (!empty($mo)) foreach ($mo as $mn => $mk) {
            $s2 = $mn ;
            $result = $this->levDist($s1, $s2) ;
            if ($result > $po->tranVal) {
              $po->tranVal = $result ;
              $po->tranId = $mn ;
              $po->str = $mk->translations[0] ;
            }
          }
        }
        foreach ($POs as $n2 => $po2){
          if ($n != $n2){
            $s2 = $po2->id ;
            $result = $this->levDist($s1, $s2) ;
            if ($result > $po2->keyVal) {
              $po->keyVal = $result ;
              $po->keyId = $po2->id ;
              $po2->keyVal = $result ;
              $po2->keyId = $po->id ;
            }
          }
        }
      }
    }

    // Get the strings that look like translation keys
    function getTranPOs(&$contents, &$mo, $domain, &$POs) {
      preg_match_all("#_[_e].*\([\'\"](.+)[\'\"]\s*,\s*[\'\"]" . $domain ."[\'\"]#",
                     $contents, $matches) ;
      $keys = array_unique($matches[1]) ;
      $keys = str_replace(array("\'", '\"', '\n'), array("'", '"', "\n"), $keys) ;
      foreach ($keys as $n => $k) {
        $v = $mo[$k] ;
        $t = $v->translations[0] ;
        $po = new PO($k, $t) ;
        $po->num = $n ;
        array_push($POs, $po) ;
      }
      $this->getClose($mo, $POs) ;
    }

    // Make a POT string from ids and msgs
    function mkPot(&$POs, $msg){
      $pot = '' ;
      $pot .=
'# This file was generated by Easy Translator Lite -- a WordPress plugin translator
# Your Name: ' . $msg["name"] . '
# Your Email: ' . $msg["email"] . '
# Your Website: ' . $msg["blog"] . '
# Your URL: ' . $msg["url"] . '
# Your Locale: ' . $msg["locale"] . '
# Your Language: ' . $msg["lang"] . '
#, fuzzy
msgid ""
msgstr ""
"Project-Id-Version: ' . $_SESSION['ezt-name'].'\n"
"PO-Revision-Date: ' . current_time('mysql') . '\n"
"Last-Translator: ' . $msg['name'] . ' <' . $msg['email'] . '>\n"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=' . $msg['charset'] . '\n"
"Content-Transfer-Encoding: 8bit\n"

' ;
      foreach ($POs as $n => $po) {
        $pot .= "msgid " . '"' . $this->decorate($po->id, "\n\r\"") . '"' . "\n" ;
        $t = $msg[$po->num] ;
        $pot .= "msgstr " . '"' . $this->decorate($t, "\n\r") . '"' . "\n\n" ;
      }
      return $pot ;
    }

    // Recursively finds all the MO files under a dir
    function rglob($pattern, $flags = 0, $path = '') {
      if (!$path && ($dir = dirname($pattern)) != '.') {
        if ($dir == '\\' || $dir == '/') $dir = '';
        return $this->rglob(basename($pattern), $flags, $dir . '/');
      }
      $paths = glob($path . '*', GLOB_ONLYDIR | GLOB_NOSORT);
      $files = glob($path . $pattern, $flags);
      foreach ($paths as $p)
        $files = array_merge($files, $this->rglob($pattern, $flags, $p . '/'));
      return $files;
    }

    // Update the PO objects in $POs with the text box stuff
    function updatePot(&$POs, $msg){
      foreach ($POs as $n => $po) {
        $t = $msg[$po->num] ;
        $po->str = $this->decorate($t, "\n\r") ;
      }
    }

    // Error messages
    function errMsg($s, $class="error", $close=true) {
      if ($class == "error") $e = "<b>Error: </b>" ;
      $s = '<div class="' . $class . '"><p>' . $e . $s . '</p></div>' ;
      if ($close) $s .= "\n</form>\n</div>\n" ;
      return $s ;
    }

    // Prints out the admin page
    function printAdminPage() {
      $locale = get_locale();
      $made = isset($_POST['ezt-make']) ;
      $saving = isset($_POST['ezt-save']) ;

      echo "\n" . '<script type="text/javascript" src="'. get_option('siteurl') .
        '/' . PLUGINDIR . '/' .  basename(dirname(__FILE__)) .
        '/wz_tooltip.js"></script>' . "\n" ;
?>
<div class="wrap" style="width:900px">
<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<h2>Easy Translator Lite</h2>

<?php
      global $l10n;
   // Button handling should happen early
      if (isset($_POST['ezt-load'])) {
        $plugin = $_POST['ezt-plugin'] ;
        $_SESSION['ezt-plugin'] = $plugin ;
        unset($_SESSION['ezt-name']);
        unset($_SESSION['ezt-mofile']) ;
        unset($_SESSION['ezt-plgdir']) ;
        unset($_SESSION['ezt-moname']) ;
        unset($_SESSION['ezt-domain']) ;
        unset($_SESSION['ezt-POs']) ;
      }

      // for a returning translator
      $plugin = $_SESSION['ezt-plugin'] ;

      if (isset($_POST['ezt-loadmo']) || isset($_POST['ezt-loadnewmo'])){
        $name = $_POST['ezt-name'];
        $_SESSION['ezt-name'] = $name ;
        $plgdir = realpath(dirname(__FILE__) . '/../' . dirname($plugin)) ;
        $_SESSION['ezt-plgdir'] = $plgdir ;
        if (isset($_POST['ezt-loadnewmo'])){
          $mofile = $_POST['ezt-newmo'] ;
          $moname = $mofile ;
        }
        else {
          $mofile = realpath($_POST['ezt-mofile']) ;
          $moname = substr($mofile,strlen($plgdir)) ;
        }
        $_SESSION['ezt-mofile'] = $mofile ;
        $_SESSION['ezt-moname'] = $moname ;
        $domain = $_POST['ezt-domain'] ;
        $_SESSION['ezt-domain'] = $domain ;
        // discard the current translation table
        unset($l10n[$domain]) ;
        load_textdomain($domain, $mofile) ;
        // discard existing POs
        unset($_SESSION['ezt-POs']) ;
      }

      $version = (float)get_bloginfo('version') ;
      if ($version < 2.80) {
        echo $this->errMsg('Sorry, Easy Translator Lite works only on WP2.8+') ;
        return ;
      }

      $plugins = get_plugins() ;
      $plugin_name = '';
      $selPlugin = '<div style="width: 15%; float:left">Select Plugin:</div>' .
        '<select style="width: 40%" name="ezt-plugin">';
      foreach ($plugins as $k => $v) {
        if ($k == $plugin) {
        	$selected = ' selected="selected" ' ;
        	$plugin_name = $v['Name'];
        }
        else $selected = '' ;
        $selPlugin .= '<option value="' . $k . '"'. $selected . '>' .
          $v['Name'] . "</option>\n" ;
      }
      $selPlugin .= '</select>' ;

      echo $selPlugin ;
      echo '<input type="hidden" name="ezt-name" value="'.$plugin_name.'" />';
      $loadPlugin = '&nbsp; <input type="submit" style="width:10%" name="ezt-load" value="Load it" /> <br />' .
        "\n"  ;
      echo $loadPlugin ;

      if (strlen($plugin) <= 0) {
        echo $this->errMsg('Select and load a plugin!', 'updated') ;
        return ;
      }

      $domain = $_SESSION['ezt-domain'] ;
      if (strlen($domain) <= 0) $domain = $plugins[$plugin]['TextDomain'] ;
      if (strlen($domain) <= 0) $domain = dirname($plugin) ;

      if (strlen($domain) <= 0) {
        echo $this->errMsg('No Text-domain!') ;
        return ;
      }

      $textDomain = '<div style="width: 15%; float:left">Text Domain:</div>' .
        '<input type="text" style="width: 40%" name="ezt-domain" ' .
        'id="domain" value="' . $domain . '" /><br />' . "\n" ;

      echo $textDomain ;

      $plgdir = $_SESSION['ezt-plgdir'] ;
      if (strlen($plgdir) <= 0)
        $plgdir = realpath(dirname(__FILE__) . '/../' . dirname($plugin)) ;
      if (strlen($plgdir) <= 0) {
        echo $this->errMsg('Sorry, cannot figure out the plugin directory!<br />' .
                    'Will be fixed in the next version.') ;
        return ;
      }

      $mofile = $_SESSION['ezt-mofile'] ;
      $moname = $_SESSION['ezt-moname'] ;

      $mofiles = $this->rglob( '/*.mo', 0, $plgdir) ;
      $mosel = '<div style="width: 15%; float:left">MO File:</div>' .
        '<select style="width: 40%" name="ezt-mofile">';
      foreach ($mofiles as $k => $v) {
        $realv = realpath($v) ;
        if ($realv == $mofile) $selected = ' selected="selected" ' ;
        else $selected = '' ;
        $mosel .= '<option value="' . $realv . '"'. $selected . '>' .
          substr($realv,strlen($plgdir)) . "</option>\n"  ;
      }
      $mosel .= '</select>' ;
      echo $mosel ;

      $loadmo = '&nbsp; <input type="submit" style="width:10%" name="ezt-loadmo" value="Load MO" />' .
        "<br />\n" ;
      echo $loadmo;

      $newmo = '<div style="width: 15%; float:left">Or Create New MO:</div>' .
        "<input type='text' name='ezt-newmo' style='width:40%'>" .
        '&nbsp; <input type="submit" style="width:10%" name="ezt-loadnewmo" value="Create MO" />' .
        "<br /><br />\n" ;

      echo $newmo ;

      if (strlen($mofile) <= 0 || strlen($moname) <= 0) {
        echo $this->errMsg('Please select and load an MO file', 'updated') ;
        return ;
      }

      if (isset($_SESSION['ezt-POs'])){
        $POs = $_SESSION['ezt-POs'];
      }
      else {
        $mo = array($l10n[$domain]->entries) ;
        $s = $this->getFileContents($plgdir) ;
        $POs = array() ;
        $this->getTranPOs($s, $mo[0], $domain, $POs) ;
        // cache the POs
        $_SESSION['ezt-POs'] = $POs ;
      }

      if (empty($POs)) {
        //  echo "<pre>" . htmlentities($s) . "</pre>" ;
        echo $this->errMsg('Please load the MO file', 'updated') ;
        return ;
      }

      // echo '<pre>' ; print_r($POs) ; echo '</pre>' ;

      if ($made) {
        $pot = htmlspecialchars($this->mkPot($POs, $_POST), ENT_QUOTES) ;
        $this->updatePot($POs, $_POST) ;
        // cache the updated POs
        $_SESSION['ezt-POs'] = $POs ;
      }
      else {
        global $current_user;
        get_currentuserinfo();
        $pot = '' ;
        $pot .= '<div style="width: 15%; float:left">Your Name:</div>' .
          '<input type="text" style="width: 30%" name="name" value="' .
          $current_user->user_firstname . " " .
          $current_user->user_lastname . '" /><br />' . "\n" ;
        $pot .= '<div style="width: 15%; float:left">Your Email:</div>' .
          '<input type="text" style="width: 30%" name="email" value="' .
          $current_user->user_email . '" /><br />' . "\n" ;
        $pot .= '<div style="width: 15%; float:left">Your Website:</div>' .
          '<input type="text" style="width: 30%" name="blog" value="' .
          get_bloginfo('blog') . '" />' . "\n<br />" ;
        $pot .= '<div style="width: 15%; float:left">Your URL:</div>' .
          '<input type="text" style="width: 30%" name="url" value="' .
          get_bloginfo('url') . '" />' . "\n<br />" ;
        $pot .= '<div style="width: 15%; float:left">Your Locale:</div>' .
          '<input type="text" style="width: 30%" name="locale" value="' .
          $locale . ' (MO: ' . $moname . ')" /><br />' . "\n" ;
        $pot .= '<div style="width: 15%; float:left">Your Language:</div>' .
          '<input type="text" style="width: 30%" name="lang" value="' .
          get_bloginfo('language') . '" /><br />' . "\n" ;
        $pot .= '<div style="width: 15%; float:left">Character Set:</div>' .
          '<input type="text" style="width: 30%" name="charset" value="' .
          get_bloginfo('charset') . '" />' . "\n<br /><br />" ;

        $pot .= '<div style="width:800px;padding:10px;padding-top:25px"></div>' ;
        $pot .= '<div style="width:38%px;paddling:10px;padding-left:100px;float:left">' .
          '<b>English (en_US)</b></div>' ;
        $pot .= '<div style="width:48%;paddling:10px;padding-left:10px;float:right">' .
          '<b>MO: ' . $moname . '</b> (' . $locale . ')</div>' ;
        $pot .= '<div style="width:100%;padding:15px"></div>' ;

        foreach ($POs as $n => $po) {
          $pot .= $po->textId() . "\n" . $po->textStr() . "\n\n" ;
        }
      }
      $makeStr =
'<div class="submit">
<input type="submit" name="ezt-make" value="Display &amp; Save POT File" title="Make a POT file with the translation strings below and display it" />&nbsp;
<input type="submit" name="ezt-clear" value="Reload Translation" title="Discard your changes and reload the translation" onclick="return confirm(\'Are you sure you want to discard your changes?\');" />&nbsp;
</div>' . $this->status . $this->error ;
      $saveStr =
'<div class="submit">
<input type="submit" name="ezt-savePot" value="Save POT file" title="Saves the strings shown below to your PC as a POT file" />&nbsp;
<input type="submit" name="ezt-mailPot" value="Mail POT file" title="Email the translation to the plugin autor" onclick="return confirm(\'Are you sure you want to email the author? Please ensure that you have the correct email address entered below.\');" />&nbsp;
<input type="submit" name="ezt-editMore" value="Edit More" title="If you are not happy with the strings, edit it further" />
</div>' . $this->status . $this->error  ;
      if ($made) {
?>
<div style="background-color:#eef;border: solid 1px #005;padding:5px">
If you are happy with the POT file as below, please save it or email it to the author.
If not, edit it further.
</div>
<?php
        echo '<input type="hidden" name="potFile" value="' .
              $domain . "-" . $locale . '.po" />' ;
        echo '<input type="hidden" name="potStr" value="' . $pot . '" />' ;
        echo $saveStr ;
        $author = $_SESSION['ezt-author'] ;
        $mail = '<div style="width: 15%; float:left">Plugin Author:</div>' .
          '<input type="text" style="width: 30%" name="ezt-author" value="' .
          $author . '" /><br />' . "\n" ;
        $authormail = $_SESSION['ezt-authormail'] ;
        // if (strlen($authormail) <= 0) $authormail = '(To email the pot file)' ;
        $mail .= '<div style="width: 15%; float:left">Author\'s Email:</div>' .
          '<input type="text" style="width: 30%" name="ezt-authormail" value="' .
          $authormail . '" />' . "\n<br /><br />" ;
        echo $mail ;
        echo  "\n" . '<pre>' . $pot . '</pre>'  . "\n</form>" ;
      }
      else
      {
?>
<div style="background-color:#eef;border: solid 1px #005;padding:5px">
You are editing <b><?php echo $moname ?></b>, hopefully in your language <b><?php echo $locale ?></b>.
<br />
Enter the translated strings in the text boxes below and hit the "Display POT File" button.
</div>
<?php
         echo $makeStr ;
         echo $pot  . "\n</form>" ;
       }
      echo "<br /><hr />" ;
      @include(dirname (__FILE__).'/myPlugins.php');
      $plgName = 'easy-translator' ;
      @include (dirname (__FILE__).'/tail-text.php');
?>
<table class="form-table" >
<tr><th scope="row"><h3><?php _e('Credits', 'easy-adsenser'); ?></h3></th></tr>
<tr><td>
<ul style="padding-left:10px;list-style-type:circle; list-style-position:inside;" >
<li>
<?php printf(__('%s uses the excellent Javascript/DHTML tooltips by %s', 'easy-adsenser'), '<b>Easy Translator Lite</b>', '<a href="http://www.walterzorn.com" target="_blank" title="Javascript, DTML Tooltips"> Walter Zorn</a>.') ;
?>
</li>
</ul>
</td>
</tr>
</table>
<?php
      echo "\n</div>" ;

    } // End function printAdminPage()

    function plugin_action($links, $file) {
      if ($file == plugin_basename(dirname(__FILE__).'/easy-translator-lite.php')){
      $settings_link = "<a href='tools.php?page=easy-translator-lite/easy-translator-lite.php'>" .
        'Lauch it' . "</a>";
      array_unshift( $links, $settings_link );
      }
      return $links;
    }
  }
} // End Class ezTran

if (class_exists("ezTran")) {
  $ezTran = new ezTran();
  if (isset($ezTran)) {
    // Add it to the Tools Menu
    if (!function_exists("ezTran_ap")) {
      function ezTran_ap() {
        global $ezTran ;
        if (function_exists('add_submenu_page'))
          add_submenu_page('tools.php','Easy Translator Lite', 'Easy Translator Lite',
                           "install_plugins", __FILE__, array(&$ezTran, 'printAdminPage'));
        add_filter('plugin_action_links', array($ezTran, 'plugin_action'), -10, 2);
      }
    }
    add_action('admin_menu', 'ezTran_ap');
  }
}
?>
