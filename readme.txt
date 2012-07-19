=== Easy Translator ===
Contributors: manojtd
Donate link: http://affiliates.thulasidas.com
Tags: plugins, internationalization, translation, translator, localization, i18n, l10n
Requires at least: 2.8
Tested up to: 3.4
Stable tag: 2.05

Easy Translator is a plugin translation tool for authors and translators. (Not a blog page translator!)

== Description ==

*Easy Translator* is a plugin to translate other plugins. It picks up translatable strings (in `_[_e]()` functions) and presents them and their existing translations (from the MO object of the current text-domain, if loaded) in a user editable form. It can generate a valid PO file that can be emailed to the plugin author. The [Pro Version](http://buy.thulasidas.com/easy-translator "Buy the Pro Version for $1.95") lets your translators email the PO file directly from the its window, streamlining their work. (Not a big deal really because you can always save the PO file and email it yourself. Still...)

If you are a plugin author interested in internationalizing your plugins, you may want to ask your potential translators to install *Easy Translator* to make it a snap to give you translations. If you want to clean up your internationalization, you will appreciate *Easy Translator* because it does a fuzzy string matching to highlight possible repetitions and conflicts among key strings.

Note that *Easy Translator* is **not** a blog page translator for a blogger. It is a tool for plugin authors and the kind international users who put in their time and effort to translate plugins. If you fall in that category, please it a go.

== Upgrade Notice ==

= 2.05 =

Minor changes to the admin page.

== Screenshots ==

1. How to launch *Easy Translator* - Where to find it?
2. How to use *Easy Translator* - The Editor
3. How to use *Easy Translator* - The POT File Viewer

== Installation ==

1. Upload the *Easy Translator* plugin (the whole easy-translator folder) to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to the Tools -> Easy Translator to use it.

== Frequently Asked Questions ==

= Looks good, but doesn't work! =

*Easy Translator* is a fairly complicated program, and it is in its infancy. If you find a bug or anything that doesn't work as expected, please do not keep it to youself. Please post it in [the forum](http://wordpress.org/tags/easy-translator-lite "Easy Translator Forum") or [email me](http://manoj.thulasidas.com/mail.shtml "Contact Manoj"). I really would like to make it work perfectly.

= This plugin conflicts with other plugins. What to do now? =

*Easy Translator* uses the PHP "super-global" variables (`$_SESSION[]`) to hold various strings and settings between your visits so that your translation work is not accidentally erased. I hope to have implemented it safely. But as any developer will tell you, there is nothing safe about using globals. If you find anything amiss, I'd appreciate it if you could let me know. Please post it in [the forum](http://wordpress.org/tags/easy-translator-lite "Easy Translator Forum") or [email me](http://manoj.thulasidas.com/mail.shtml "Contact Manoj").

= The PO files created generate errors when I run `msgfmt` on them. What are these errors? =

The plugin has a known issue with quotation marks within strings. Some of the quotations marks don't get escaped with a backslash. I think this issue is locale-specific, and don't know how to solve it. If you have any ideas, please let me know. The workaround is to edit the generated PO files with a text editor and go to the lines that give you errors in `msgfmt`. You will most likely find some unescaped quotes that you should prepend a backslash to. It should then go through `msgfmt` without error. Another known problem is that in some locales, the `\n` character in the first few lines appear as `n` -- again in a locale-specific way.

== Change Log ==

= Past =

* V2.05: Minor changes to the admin page. [July 18, 2012]
* V2.04: Taking care of magic_quotes_gpc in PHP. [May 18, 2012]
* V2.03: Renaming the plugin to drop the word Lite. [May 11, 2012]
* V2.02: Changing the php file search to recursive. [Mar 20, 2012]
* V2.01: Adding a new feature to create a new MO file (rather than just editing an existing one). [Mar 19, 2012]
* V2.00: Releasing a Lite and Pro version. [Mar 6, 2012]
* V1.01: Correcting a few minor bugs (a) Author email (of the plugin being translated was set to the author of this plugin. (b) The name of the plugin was set to *Easy AdSense* (Thanks, Sub!). (c) Some corrections are needed for escaping quotation marks and line breaks in locales other than English. Will include them in the next release. (Hard to test because my locale is en_US).
* V1.00: Initial release. [July 21, 2009]

