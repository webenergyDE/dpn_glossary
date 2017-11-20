<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'dpn_glossary',
    'Glossarylist',
    'LLL:EXT:dpn_glossary/Resources/Private/Language/locallang.xlf:tx_dpnglossary.wizard_list_title'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'dpn_glossary',
    'Glossarypreview',
    'LLL:EXT:dpn_glossary/Resources/Private/Language/locallang.xlf:tx_dpnglossary.wizard_preview_title'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'dpn_glossary',
    'Glossarydetail',
    'LLL:EXT:dpn_glossary/Resources/Private/Language/locallang.xlf:tx_dpnglossary.wizard_detail_title'
);

$extensionName = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase('dpn_glossary');
$pluginSignature = strtolower($extensionName) . '_glossarypreview';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    $pluginSignature,
    'FILE:EXT:dpn_glossary/Configuration/FlexForms/flexform_preview.xml'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:dpn_glossary/Configuration/TSconfig/ContentElementWizard.t3s">');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'dpn_glossary',
    'Configuration/TypoScript',
    'dreipunktnull Glossar'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_dpnglossary_domain_model_term');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_dpnglossary_domain_model_description');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_dpnglossary_domain_model_synonym');
