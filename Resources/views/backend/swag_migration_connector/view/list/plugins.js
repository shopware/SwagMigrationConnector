/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

// {namespace name=backend/swag_migration_connector/main}
Ext.define('Shopware.apps.SwagMigrationConnector.view.list.Plugins', {
    extend: 'Ext.grid.Panel',
    border: false,
    title: 'Plugins',

    initComponent: function () {
        this.columns = this.getColumns();
        this.callParent(arguments);
    },

    getColumns: function () {
        return [
            {
                header: '{s name="columns/plugin"}Plugin{/s}',
                dataIndex: 'name',
                flex: 0.7
            },
            {
                header: '{s name="columns/available"}{/s}',
                dataIndex: 'active',
                renderer: this.availableRenderer,
                flex: 0.5
            }, {
                header: '{s name="columns/message"}Message{/s}',
                renderer: this.messageRenderer,
                dataIndex: 'message',
                flex: 3
            }, {
                xtype: 'actioncolumn',
                width: 26,
                items: [
                    {
                        iconCls: 'sprite-balloon-ellipsis',
                        tooltip: '{s name="action/show_another_plugins"}{/s}',
                        handler: function (view, rowIndex, colIndex, item, opts, record) {
                            Ext.create('Shopware.apps.SwagMigrationConnector.view.plugin.Window', {
                                store: record.getRecommendations()
                            })
                        },
                        getClass: function (view, metadata, record) {
                            if (record.getRecommendations().count() <= 1 && record.getRecommendations().first().get('type') !== 'recommendationByShopware') {
                                return 'x-hidden';
                            }
                        }
                    }
                ]
            }
        ];
    },

    availableRenderer: function (value, metaData, record) {
        var recommend = record.getRecommendations().first();

        var divClass,
            divStyle = 'style="width: 16px; height: 16px; margin: 0 auto;"';

        if (recommend.get('type') === 'successorPlanned' || recommend.get('type') === 'recommendationByShopware') {
            divClass = 'class="sprite-exclamation"';
        } else if (recommend.get('type') === 'noSuccessor') {
            divClass = 'class="sprite-cross"';
        } else {
            divClass = 'class="sprite-tick"';
        }

        return '<div ' + [divClass, divStyle].join(' ') + '></div>';
    },

    messageRenderer: function (value, metaData, record) {
        var recommand = record.getRecommendations().first(),
            text = '';

        if (recommand.get('type') === 'successorPlanned') {
            text = '{s name="type/successorPlanned"}{/s}';

            if (recommand.get('plannedReleaseDate')) {
                text = Ext.String.format('{s name="type/successorPlannedWithReleaseDate"}{/s}', Ext.Date.format(recommand.get('plannedReleaseDate'), 'F Y'));
            }
        } else if (recommand.get('type') === 'noSuccessor') {
            text = '{s name="type/noSuccessor"}{/s}';
        } else if (recommand.get('type') === 'recommendationByShopware') {
            text = '{s name="type/recommendationByShopware"}{/s}';
        } else if (recommand.get('type') === 'targetPluginReleased') {
            text = '{s name="type/targetPluginReleased"}{/s}';
        } else if (recommand.get('type') === 'coreIntegrated') {
            text = '{s name="type/coreIntegrated"}{/s}';
        }

        return text;
    }
});
