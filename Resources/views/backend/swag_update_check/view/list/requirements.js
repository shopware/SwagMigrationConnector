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

// {namespace name=backend/swag_update_check/main}
Ext.define('Shopware.apps.SwagUpdateCheck.view.list.Requirements', {
    extend: 'Ext.grid.Panel',
    border: false,
    title: '{s name="tabs/requirements"}Requirements{/s}',

    initComponent: function () {
        this.columns = this.getColumns();
        this.callParent(arguments);
    },

    getColumns: function () {
        return [
            {
                xtype: 'actioncolumn',
                width: 60,
                header: '{s name="columns/status"}Status{/s}',
                items: [{
                    getClass: function (value, metadata, record) {
                        if (record.get('validation') === 1) {
                            return 'sprite-tick';
                        } else if (record.get('validation') === 1) {
                            return 'sprite-exclamation';
                        } else {
                            return 'sprite-cross';
                        }
                    }
                }]
            },
            {
                header: '{s name="columns/message"}Message{/s}',
                dataIndex: 'message',
                flex: 2,
                allowHtml: true
            }
        ];
    },

    availableRenderer: function (value, metaData, record) {
        var recommand = record.getRecommandations().first();

        var divClass,
            divStyle = 'style="width: 16px; height: 16px; margin: 0 auto;"';

        if (recommand.get('type') === 'successorPlanned') {
            divClass = 'class="sprite-exclamation"';
        } else if (recommand.get('type') === 'noSuccessor') {
            divClass = 'class="sprite-cross"';
        } else {
            divClass = 'class="sprite-tick"';
        }

        return '<div ' + [divClass, divStyle].join(' ') + '></div>';
    },

    messageRenderer: function (value, metaData, record) {
        var recommand = record.getRecommandations().first(),
            text = '';

        if (recommand.get('type') === 'successorPlanned') {
            text = '{s name="type/successorPlanned"}{/s}';
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
