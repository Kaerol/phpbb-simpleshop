<?php

/**
 * DO NOT CHANGE
 */

if (!defined('IN_PHPBB')) {
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can ( and should ) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

// Bot settings

$lang = array_merge($lang, array(
    'KAEROL_SIMPLESHOP'                                 => 'Sklepik',
    'KAEROL_SIMPLESHOP_TITLE'                           => 'Co sprzedajesz',
    'KAEROL_SIMPLESHOP_PRODUCTS'                        => 'Asortyment',
    'KAEROL_SIMPLESHOP_PRODUCTS_DESC_1'                 => 'Każdy produkt należy umieścić w osobnym wierszu. Należy rozdzielić rozmiary, kolory, wzory, itp,.',
    'KAEROL_SIMPLESHOP_PRODUCTS_DESC_2'                 => 'Można maksymalnie określić ',
    'KAEROL_SIMPLESHOP_PRODUCTS_DESC_3'                 => 'opcji.',
    'KAEROL_SIMPLESHOP_PRODUCTS_UPDATE'                 => 'Aby zaktualizować ilość należy podać nową ilość',
    'KAEROL_SIMPLESHOP_PRODUCTS_DELETE'                 => 'Aby usunąć zakup należy podać 0 (zero)',
    'KAEROL_SIMPLESHOP_ORDERS_COLLECT_END_DATE'         => 'Data do',
    'KAEROL_SIMPLESHOP_ORDERS_COLLECT_END_DATE_INFO'    => 'Termin składania zamówień',
    'KAEROL_SIMPLESHOP_ORDERS_COLLECT_END_DATE_DESC_1'  => 'Zamówienia należy składać w nieprzekraczalnym terminie do dnia',
    'KAEROL_SIMPLESHOP_ORDERS_COLLECT_END_DATE_DESC_2'  => 'włącznie.',
    'KAEROL_SIMPLESHOP_ORDERS_COLLECT_BUTTON_INFO'      => 'Aby złożyć zamówienie skorzystaj z formularza na samej górze postu.',
    'KAEROL_SIMPLESHOP_ORDER'                           => 'WYŚLIJ ZAMÓWIENIE',
    'KAEROL_SIMPLESHOP_INFO'                            => 'INFORMACJA!',
    'KAEROL_SIMPLESHOP_WARNING'                         => 'UWAGA!',
    'KAEROL_SIMPLESHOP_ITEMS_REPORT'                    => 'Zobacz raport przedmiotów',
    'KAEROL_SIMPLESHOP_PERSON_REPORT'                   => 'Zobacz raport użytkowników',
    'KAEROL_SIMPLESHOP_ITEMS_REPORT_TITLE'              => 'Raport przedmiotów',
    'KAEROL_SIMPLESHOP_PERSON_REPORT_TITLE'             => 'Raport użytkowników',
    'KAEROL_SIMPLESHOP_REPORT_USERNAME_HEADER'          => 'Nazwa użytkownika',
    'KAEROL_SIMPLESHOP_REPORT_NAME_HEADER'              => 'Nazwa przedmiotu',
    'KAEROL_SIMPLESHOP_REPORT_COUNT_HEADER'             => 'Ilość',
    'KAEROL_SIMPLESHOP_REPORT_SUMMARY'				    => 'Suma',
    'KAEROL_SIMPLESHOP_ORDER_ALL_LABEL'                 => 'Zamówione %s',
    'KAEROL_SIMPLESHOP_ORDER_USER_LABEL'                => '( w tym Twoje %s)',
    'KAEROL_SIMPLESHOP_TOGGLE_ORDER_REPORT'             => 'Schowaj raport/Pokaż zamówienie',
    'KAEROL_SIMPLESHOP_SKIPPED_UPDATE_ORDERS_EXIST'		=> 'Nie można edytować bo, już jest zamowienie.',
    'KAEROL_SIMPLESHOP_ORDERS_COLLECT_ENDED'	        => 'Zbieranie zamówień zostało zakończone z dniem',
));
