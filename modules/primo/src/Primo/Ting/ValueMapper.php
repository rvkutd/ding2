<?php

namespace Primo\Ting;

use Matriphe\ISO639\ISO639;

/**
 * Utility class for mapping various values between Ding and Primo.
 *
 * @package Primo\Ting
 */
class ValueMapper {
  // Don't use any context for languages, piggyback on cores own translation.
  // See mapLanguageToISO639() for the consequences of this.
  const T_CONTEXT_LANGUAGE = NULL;

  /**
   * The t() context used for primo genres.
   */
  const T_CONTEXT_GENRE = 'Primo Genre code';

  /**
   * Maps names of languages in ISO639 to translated names.
   *
   * The mapping is done in two parts, first the langcode is mapped to its full
   * english language name. If this fails the original langcode is returned.
   *
   * The english language-name is then translated into the local language, if
   * this fails the english language name is returned.
   *
   * @param string $langcode
   *   ISO639 language code.
   *
   * @return string
   *   The mapped language or the input languagecode or the english language-
   *   name in case of failed mapping.
   */
  public static function mapLanguageFromIso639($langcode) {
    // Languages returned by Primo is in ISO-639 format. To return something
    // understandable by users we convert it to English and let Drupal try to
    // translate it to the user language.
    $lang = (new ISO639())->languageByCode2b($langcode);

    // Return the langcode if we failed to look up the language.
    if (empty($lang)) {
      return $langcode;
    }

    // Do the translation, the english language name will be returned in case
    // we can't do the translation.
    return t($lang, [], ['context' => static::T_CONTEXT_LANGUAGE]);
  }

  /**
   * Maps translated languages back to a ISO639 language.
   *
   * @param string $language
   *   A language mapped via mapLanguageFromIso639().
   *
   * @return string
   *   The mapped language or original input value in case the mapping fails.
   */
  public static function mapLanguageToIso639($language) {
    // We assume this string came from mapLanguageFromIso639() in which case it
    // can be a translated language, the english language name or a unmapped
    // language.
    // If the original language could not be mapped we might be heading into
    // trouble. Eg. if the mapping failed and $language is actually a 3-lettered
    // langcode that happens to be a valid word in the default language, the
    // attempt to reverse-translate might leave us with something that is far
    // from a valid langcode.
    // All of this can happen as T_CONTEXT_LANGUAGE is currently NULL eg. we're
    // using cores default translations. If we set T_CONTEXT_LANGUAGE to a
    // custom value we'll avoid all of this, but will have to translate all
    // possible languages in this context.
    // Attempt to map the language back to its source.
    $source = static::reverseTranslate($language, static::T_CONTEXT_LANGUAGE);

    // If this fails we get the input back ($source is now a langcode or a
    // english language name), if it succeeds we have a english language name.
    // In both cases it is somewhat safe to attempt to do the mapping back to
    // ISO639 so go ahead and do it.
    $langcode = (new ISO639())->code2bByLanguage($source);

    if (empty($langcode)) {
      // Something went wrong. In case the issue was that $language was actually
      // a langcode, return it.
      return $langcode;
    }

    // Mapping completed, return it.
    return $langcode;
  }

  /**
   * Maps primo genere codes to their mapped counterpart.
   *
   * @param string $code
   *   The Primo code.
   *
   * @return string
   *   The translated code or the input code if it could not be mapped.
   */
  public static function mapGenreFromCode($code) {
    return t($code, [], ['context' => static::T_CONTEXT_GENRE]);
  }

  /**
   * @param $genre
   *
   * @return false|int|string
   */
  public static function mapGenreToCode($genre) {
    return static::reverseTranslate($genre, static::T_CONTEXT_GENRE);
  }

  /**
   * Maps primo material type codes to their mapped counterpart.
   *
   * @param string $code
   *   The Primo code.
   *
   * @return string
   *   The mapped code or the input code if it could not be mapped.
   */
  public static function mapMaterialTypeFromCode($code) {
    $map = variable_get('primo_material_type_map', []);
    return (!empty($map[$code])) ? $map[$code] : $code;
  }

  /**
   * Maps material types to their mapped Primo code.
   *
   * @param string $material_type
   *   The material type.
   *
   * @return string
   *   The translated code or the input code if it could not be mapped.
   */
  public static function mapMaterialTypeToCode($material_type) {
    $map = array_flip(variable_get('primo_material_type_map', []));
    return (!empty($map[$material_type])) ? $map[$material_type] : $code;
  }

  /**
   * Reverse translation.
   *
   * "Translates" translated strings back to their source string.
   *
   * WARNING: Will not work for strings with params.
   *
   * Credits: http://www.open-craft.com/blog/reverse-translating-string-back-english-drupal
   */
  public static function reverseTranslate($string, $context = NULL) {
    global $language;
    $langcode = $language->language;

    // Notice: We're skipping support for custom translations done directly in
    // settings.php here.
    // Bail out if we don't have the locale module.
    if (!function_exists('locale') || $langcode === 'en') {
      return $string;
    }

    // Lookup locale translations.
    if (variable_get('locale_cache_strings', 1) == 1) {
      // Cache is on: this string is in the static cache for sure since we have
      // its translated version.
      $locale_t = locale();

      if (isset($locale_t[$langcode][$context]) && ($source = array_search($string, $locale_t[$langcode][$context], TRUE))) {
        return $source;
      }
      else {
        return $string;
      }
    }
    else {
      // Cache is off: look it up in the database.
      $source = db_query("SELECT s.source FROM {locales_source} s LEFT JOIN {locales_target} t ON s.lid = t.lid AND t.language = :language WHERE t.translation = :translation AND s.context = :context AND s.textgroup = 'default'",
        [
          ':language' => $langcode,
          ':translation' => $string,
          ':context' => (string) $context,
        ]
      )->fetchObject();
      if ($source) {
        return $source->source;
      }

      // Lookup completed return.
      return $string;
    }
  }

}
