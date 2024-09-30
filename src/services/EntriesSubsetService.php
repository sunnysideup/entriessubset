<?php

/**
 * Entris Subset Service
 *
 * @link      http://n43.me
 * @copyright Copyright (c) 2017 Nathaniel Hammond (nfourtythree)
 **/

namespace nfourtythree\entriessubset\services;

use Craft;

use craft\base\Component;
use craft\elements\User;
use craft\helpers\ArrayHelper;
use craft\models\EntryType;
use craft\models\Section;
use yii\base\InvalidConfigException;

/**
 * Entries Subset Service
 *
 * @author Nathaniel Hammond (nfourtythree)
 * @package EntriesSubset
 * @since 1.0.0
 *
 * @property-read array $userGroups
 * @property-read array $entryTypes
 * @property-read array[]|array $entryTypeOptions
 * @property-read array $users
 */
class EntriesSubsetService extends Component
{
    /**
     * @return array
     */
    public function getEntryTypes(): array
    {
        return ArrayHelper::index(Craft::$app->getEntries()->getAllEntryTypes(), 'handle');
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    public function getEntryTypeOptions(): array
    {
        $entryTypeOptions = array_map(function(EntryType $et) {

            $name = Craft::t('site', $et->name);
            $usages = array_filter($et->findUsages(), fn($usage) => $usage instanceof Section);
            if (!empty($usages)) {
                $name = Craft::t('site', '{entryType} ({sections})', [
                    'entryType' => $name,
                    'sections' => implode(', ', array_map(fn(Section $section) => $section->name, $usages))
                ]);
            }

            return ['label' => $name, 'value' => $et->uid];
        }, Craft::$app->getEntries()->getAllEntryTypes());

        ArrayHelper::multisort($entryTypeOptions, 'label');

        return $entryTypeOptions;
    }

    /**
     * Get user groups
     *
     * @return array an array of user groups keyed by the handle
     */
    public function getUserGroups(): array
    {
        $userGroups = [];
        $allUserGroups = Craft::$app->getUserGroups()->getAllGroups();

        if (count($allUserGroups)) {
            $userGroups = ArrayHelper::map($allUserGroups, 'id', 'name');
        }

        return $userGroups;
    }

    /**
     * Get Users
     * @return array Array of users keyed by their ID
     */
    public function getUsers(): array
    {
        $users = [];
        $allUsers = User::find()->all();

        if (count($allUsers)) {
            $users = ArrayHelper::index($allUsers, 'id');

            foreach ($users as $id => $user) {
                $users[$id] = implode(' - ', array_filter([$user->fullName, ($user->email != $user->username) ? $user->username : '', $user->email]));
            }
        }

        return $users;
    }
}
