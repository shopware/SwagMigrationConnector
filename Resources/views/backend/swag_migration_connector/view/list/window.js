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
Ext.define('Shopware.apps.SwagMigrationConnector.view.list.Window', {

    extend: 'Enlight.app.Window',

    alias: 'widget.update-main-window',

    cls: Ext.baseCSSPrefix + 'swag-update-window',

    layout: 'fit',

    width: 1000,
    height: 715,

    bodyStyle: 'border-bottom-width: 0 !important; border-radius: 0; -webkit-border-radius: 0; -moz-border-radius: 0;',

    title: '{s name="window_title"}Update Check{/s}',

    requirementsStore: null,

    pluginsStore: null,

    autoShow: true,

    initComponent: function () {
        var me = this;

        me.items = [
            me.createTabPanel(),
        ];

        me.dockedItems = [ me.createToolbar() ];

        me.callParent(arguments);
    },

    /**
     * @return { Ext.tab.Panel }
     */
    createTabPanel: function() {
        this.tabPanel = Ext.create('Ext.tab.Panel', {
            flex: 1,
            items: [
                Ext.create('Shopware.apps.SwagMigrationConnector.view.list.Info'),
                Ext.create('Shopware.apps.SwagMigrationConnector.view.list.Requirements', {
                    store: this.requirementsStore,
                }),
                Ext.create('Shopware.apps.SwagMigrationConnector.view.list.Plugins', {
                    store: this.pluginsStore,
                }),
            ]
        });

        return this.tabPanel;
    },

    createToolbar: function() {
        var me = this;

        me.cancelButton = Ext.create('Ext.button.Button', {
            cls: 'secondary',
            text: '{s name="cancel"}Cancel{/s}',
            handler: function() {
                me.destroy();
            }
        });

        me.updateButton = Ext.create('Ext.button.Button', {
            cls: 'primary',
            text: '{s name="start_update"}zum Shopware Account{/s}',
            handler: function() {
                window.open('https://account.shopware.com/shops/shops')
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            border: false,
            dock: 'bottom',
            items: [ '->', me.cancelButton, me.updateButton ]
        });
    }
});
