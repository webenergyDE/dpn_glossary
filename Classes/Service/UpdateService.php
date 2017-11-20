<?php
namespace Featdd\DpnGlossary\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Daniel Dorndorf <dorndorf@featdd.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class UpdateService implements SingletonInterface
{
    /**
     * @var \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected $databaseConnection;

    /**
     * @var string
     */
    protected $currentVersion;

    /**
     * @var array
     */
    protected $updateChecks = [];

    /**
     * @return \Featdd\DpnGlossary\Service\UpdateService
     */
    public function __construct()
    {
        $this->databaseConnection = $GLOBALS['TYPO3_DB'];
        $this->currentVersion = ExtensionManagementUtility::getExtensionVersion('dpn_glossary');
    }

    /**
     * @return bool
     */
    public function isUpdateNecessary()
    {
        $this->checkUpdatesNecessarity();

        return !empty($this->updateChecks);
    }

    /**
     */
    public function makeUpdates()
    {
        foreach ($this->updateChecks as $updateMethod => $updateNotice) {
            if (method_exists($this, $updateMethod)) {
                call_user_func([$this, $updateMethod]);
            }
        }
    }

    /**
     * @return string
     */
    public function getUpdateNotices()
    {
        $updateNotices = '<ul>' . PHP_EOL;

        foreach ($this->updateChecks as $updateNotice) {
            $updateNotices .= '<li>' . $updateNotice . '</li>' . PHP_EOL;
        }

        $updateNotices .= '</ul>';

        return $updateNotices;
    }

    /**
     */
    protected function checkUpdatesNecessarity()
    {
        $this->checkMissingDescriptions();
        $this->checkMediaColumn();
        $this->checkSortingColumn();
        $this->checkSynonymTable();
        $this->checkExcludeFromParsingColumn();
        $this->checkTermModeAndTermLinkColumn();
    }

    /**
     */
    protected function checkMissingDescriptions()
    {
        /** @var \mysqli_result $checkSortingColumn */
        $check = $this->databaseConnection->sql_query('SHOW COLUMNS FROM tx_dpnglossary_domain_model_term LIKE "descriptions"');

        if (0 === $check->num_rows) {
            $this->updateChecks['updateMissingDescriptions'] = 'The missing "Descriptions" column & table was added';
        }
    }

    protected function updateMissingDescriptions()
    {
        $this->databaseConnection->sql_query('
            ALTER TABLE tx_dpnglossary_domain_model_term
            ADD descriptions INT(11) UNSIGNED DEFAULT \'0\'
        ');
        $this->databaseConnection->sql_query('
            CREATE TABLE tx_dpnglossary_domain_model_description (

                uid              INT(11)                           NOT NULL AUTO_INCREMENT,
                pid              INT(11) DEFAULT \'0\'             NOT NULL,
            
                sorting          INT(11) UNSIGNED DEFAULT \'0\'    NOT NULL,
            
                term             INT(11) UNSIGNED DEFAULT \'0\'    NOT NULL,
                meaning          VARCHAR(255) DEFAULT \'\'         NOT NULL,
                text             TEXT                              NOT NULL,
            
                tstamp           INT(11) UNSIGNED DEFAULT \'0\'    NOT NULL,
                crdate           INT(11) UNSIGNED DEFAULT \'0\'    NOT NULL,
                cruser_id        INT(11) UNSIGNED DEFAULT \'0\'    NOT NULL,
                deleted          TINYINT(4) UNSIGNED DEFAULT \'0\' NOT NULL,
                hidden           TINYINT(4) UNSIGNED DEFAULT \'0\' NOT NULL,
                starttime        INT(11) UNSIGNED DEFAULT \'0\'    NOT NULL,
                endtime          INT(11) UNSIGNED DEFAULT \'0\'    NOT NULL,
            
                t3ver_oid        INT(11) DEFAULT \'0\'             NOT NULL,
                t3ver_id         INT(11) DEFAULT \'0\'             NOT NULL,
                t3ver_wsid       INT(11) DEFAULT \'0\'             NOT NULL,
                t3ver_label      VARCHAR(255) DEFAULT \'\'         NOT NULL,
                t3ver_state      TINYINT(4) DEFAULT \'0\'          NOT NULL,
                t3ver_stage      INT(11) DEFAULT \'0\'             NOT NULL,
                t3ver_count      INT(11) DEFAULT \'0\'             NOT NULL,
                t3ver_tstamp     INT(11) DEFAULT \'0\'             NOT NULL,
                t3ver_move_id    INT(11) DEFAULT \'0\'             NOT NULL,
            
                t3_origuid       INT(11) DEFAULT \'0\'             NOT NULL,
                sys_language_uid INT(11) DEFAULT \'0\'             NOT NULL,
                l10n_parent      INT(11) DEFAULT \'0\'             NOT NULL,
                l10n_diffsource  MEDIUMBLOB,
            
                PRIMARY KEY (uid),
                KEY parent (pid),
                KEY t3ver_oid (t3ver_oid,t3ver_wsid),
                KEY language (l10n_parent,sys_language_uid)
            );
        ');

        $results = $this->databaseConnection->exec_SELECTquery(
            'uid,tooltiptext,description',
            'tx_dpnglossary_domain_model_term',
            ''
        );

        foreach ($results as $result) {
            $this->databaseConnection->exec_INSERTquery('tx_dpnglossary_domain_model_description', [
                'term' => $result['uid'],
                'meaning' => $result['tooltiptext'],
                'text' => $result['description'],
            ]);
        }

        $this->databaseConnection->exec_UPDATEquery(
            'tx_dpnglossary_domain_model_term',
            '',
            ['descriptions' => 1]
        );

        $this->databaseConnection->sql_query('
            ALTER TABLE tx_dpnglossary_domain_model_term
            CHANGE description zzz_deleted_description TEXT NOT NULL
        ');
    }

    /**
     */
    protected function checkSortingColumn()
    {
        /** @var \mysqli_result $checkSortingColumn */
        $check = $this->databaseConnection->sql_query('SHOW COLUMNS FROM tx_dpnglossary_domain_model_description LIKE "sorting"');

        if (0 === $check->num_rows) {
            $this->updateChecks['updateSortingColumn'] = 'The missing sorting column was added to descriptions';
        }
    }

    /**
     */
    protected function updateSortingColumn()
    {
        $this->databaseConnection->sql_query('
            ALTER TABLE tx_dpnglossary_domain_model_description
            ADD sorting INT(11) UNSIGNED DEFAULT \'0\' NOT NULL
        ');
    }

    /**
     */
    protected function checkMediaColumn()
    {
        /** @var \mysqli_result $checkSortingColumn */
        $check = $this->databaseConnection->sql_query('SHOW COLUMNS FROM tx_dpnglossary_domain_model_description LIKE "images"');

        if (0 < $check->num_rows) {
            $this->updateChecks['updateMediaColumn'] = 'Images field changed to media field';
        }
    }

    /**
     */
    protected function updateMediaColumn()
    {
        $this->databaseConnection->sql_query('
            ALTER TABLE tx_dpnglossary_domain_model_term
            CHANGE COLUMN images media INT(11) UNSIGNED DEFAULT \'0\';
        ');
    }

    /**
     */
    protected function checkSynonymTable()
    {
        /** @var \mysqli_result $checkSortingColumn */
        $check = $this->databaseConnection->sql_query('SHOW TABLES LIKE "tx_dpnglossary_domain_model_synonym"');

        if (0 === $check->num_rows) {
            $this->updateChecks['updateSynonymTable'] = 'Added synonym table to your database';
        }
    }

    /**
     */
    protected function updateSynonymTable()
    {
        $this->databaseConnection->sql_query('
            CREATE TABLE tx_dpnglossary_domain_model_synonym (
            
                uid              INT(11)                           NOT NULL AUTO_INCREMENT,
                pid              INT(11) DEFAULT \'0\'             NOT NULL,
            
                sorting          INT(11) UNSIGNED DEFAULT \'0\'    NOT NULL,
            
                term             INT(11) DEFAULT \'0\'             NOT NULL,
                name             VARCHAR(255) DEFAULT \'\'         NOT NULL,
            
                tstamp           INT(11) UNSIGNED DEFAULT \'0\'    NOT NULL,
                crdate           INT(11) UNSIGNED DEFAULT \'0\'    NOT NULL,
                cruser_id        INT(11) UNSIGNED DEFAULT \'0\'    NOT NULL,
                deleted          TINYINT(4) UNSIGNED DEFAULT \'0\' NOT NULL,
                hidden           TINYINT(4) UNSIGNED DEFAULT \'0\' NOT NULL,
                starttime        INT(11) UNSIGNED DEFAULT \'0\'    NOT NULL,
                endtime          INT(11) UNSIGNED DEFAULT \'0\'    NOT NULL,
            
                t3ver_oid        INT(11) DEFAULT \'0\'             NOT NULL,
                t3ver_id         INT(11) DEFAULT \'0\'             NOT NULL,
                t3ver_wsid       INT(11) DEFAULT \'0\'             NOT NULL,
                t3ver_label      VARCHAR(255) DEFAULT \'\'         NOT NULL,
                t3ver_state      TINYINT(4) DEFAULT \'0\'          NOT NULL,
                t3ver_stage      INT(11) DEFAULT \'0\'             NOT NULL,
                t3ver_count      INT(11) DEFAULT \'0\'             NOT NULL,
                t3ver_tstamp     INT(11) DEFAULT \'0\'             NOT NULL,
                t3ver_move_id    INT(11) DEFAULT \'0\'             NOT NULL,
            
                t3_origuid       INT(11) DEFAULT \'0\'             NOT NULL,
                sys_language_uid INT(11) DEFAULT \'0\'             NOT NULL,
                l10n_parent      INT(11) DEFAULT \'0\'             NOT NULL,
                l10n_diffsource  MEDIUMBLOB,
            
                PRIMARY KEY (uid),
                KEY parent (pid),
                KEY t3ver_oid (t3ver_oid,t3ver_wsid),
                KEY language (l10n_parent,sys_language_uid)
            );
        ');
    }

    /**
     */
    protected function checkExcludeFromParsingColumn()
    {
        /** @var \mysqli_result $checkSortingColumn */
        $check = $this->databaseConnection->sql_query('SHOW COLUMNS FROM tx_dpnglossary_domain_model_term LIKE "exclude_from_parsing"');

        if (0 === $check->num_rows) {
            $this->updateChecks['updateExcludeFromParsingColumn'] = 'Add missing exclude_from_parsing column';
        }
    }

    /**
     */
    protected function updateExcludeFromParsingColumn()
    {
        $this->databaseConnection->sql_query('
            ALTER TABLE tx_dpnglossary_domain_model_term
            ADD exclude_from_parsing tinyint(4) unsigned DEFAULT \'0\' NOT NULL
        ');
    }

    /**
     */
    protected function checkTermModeAndTermLinkColumn()
    {
        /** @var \mysqli_result $checkSortingColumn */
        $check = $this->databaseConnection->sql_query('SHOW COLUMNS FROM tx_dpnglossary_domain_model_term LIKE "term_mode"');

        if (0 === $check->num_rows) {
            $this->updateChecks['updateTermModeAndTermLinkColumn'] = 'Add missing term_mode & term_link column';
        }
    }

    /**
     */
    protected function updateTermModeAndTermLinkColumn()
    {
        $this->databaseConnection->sql_query('
            ALTER TABLE tx_dpnglossary_domain_model_term
            ADD term_mode varchar(255) DEFAULT \'\'  NOT NULL,
            ADD term_link varchar(255) DEFAULT \'\'  NOT NULL
        ');
    }
}
