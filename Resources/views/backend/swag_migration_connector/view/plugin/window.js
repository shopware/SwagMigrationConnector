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
Ext.define('Shopware.apps.SwagMigrationConnector.view.plugin.Window', {
    extend: 'Enlight.app.Window',
    alias: 'widget.update-plugin-window',
    cls: Ext.baseCSSPrefix + 'swag-update-window',
    layout: 'fit',
    width: 1000,
    height: 300,
    bodyStyle: 'border-bottom-width: 0 !important; border-radius: 0; -webkit-border-radius: 0; -moz-border-radius: 0;',
    title: '{s name="plugin_detail/window"}{/s}',
    autoShow: true,
    autoScroll: true,

    initComponent: function () {
        var me = this;

        me.items = [
            me.createGrid(),
        ];

        me.callParent(arguments);
    },

    /**
     * @return { Ext.tab.Panel }
     */
    createGrid: function() {
        this.tabPanel = Ext.create('Ext.grid.Panel', {
            store: this.store,
            columns: [
                {
                    header: 'Name',
                    dataIndex: 'name',
                    renderer: this.renderName,
                    flex: 1
                },
                {
                    header: 'Nachricht',
                    dataIndex: 'name',
                    renderer: this.renderType,
                    flex: 1
                }
            ]
        });

        return this.tabPanel;
    },

    renderName: function (value, metaData, record) {
        var plugin = record.getTargetPlugin().first();

        if (plugin.get('storeLink') === null) {
            return plugin.get('localizedName');
        }

        return '<a href="' + plugin.get('storeLink') + '" target="_blank">' + plugin.get('localizedName') + '</a>';
    },

    renderType: function (value, metaData, record) {
        if (record.get('type') === 'recommendationByShopware') {
            return '{s name="plugin_detail/recommendationByShopware"}{/s}';
        } else if (record.get('type') === 'noSuccessor') {
            return '{s name="plugin_detail/noSuccessor"}{/s}';
        } else if (record.get('type') === 'targetPluginReleased') {
            return '{s name="plugin_detail/targetPluginReleased"}{/s}';
        } else if (record.get('type') === 'successorPlanned') {
            return '{s name="plugin_detail/successorPlanned"}{/s}';
        }

        return '';
    }
});
