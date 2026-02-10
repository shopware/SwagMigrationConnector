# Shopware Migration Connector (SwagMigrationConnector)

## A plugin which delivers new API endpoints for fast data reads.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Description

The Migration Connector connects your Shopware 5 shop with Shopware 6 and checks if your Shopware 5 system is ready for data migration. You can migrate a large amount of data from your existing Shopware 5 installation to Shopware 6, including products, manufacturers, customers, etc., and update it at any time. This makes it possible to use both systems and make the transition as easy as possible.

The Migration Connector provides API endpoints that allow Shopware 6 to create a secure data connection with the active Shopware 5 shop.

## Important:

The connection does not modify any data in your active shop. Nevertheless, it is always recommended that you make a backup of your active shop before installing new plugins. With this plugin, all processes take place in the background - no settings or inputs are required. As long as you work with Shopware 6, you should leave the plugin activated. This is the only way to update your data at any given time.

## It's that easy to connect your Shopware 5 Shop with Shopware 6: 

- Download the Migration Connector from the Community Store.
- Install and then activate the plugin via the Plugin Manager in your shop.
- In Shopware 6, you use the Migration Assistant plugin to create an API connection and enter the API key* of your shop. Your username (API user) and your shop domain are also required.

## Release Process

### 1. Prepare the release

1. Update the version number in `plugin.xml` to match the new release version.
2. Update the changelog in `plugin.xml` with the changes included in this release.
3. Commit and push these changes to the repository.
4. Make sure the repository is clean and all changes are merged before proceeding to the next steps.

### 2. Clone the repository again

```bash
git clone git@github.com:shopware/SwagMigrationConnector.git
cd SwagMigrationConnector
```

### 3. Remove blacklisted files and folders

Delete all files and folders listed in `.sw-zip-blacklist`:

Also remove files not needed for the release:

```bash
rm -rf .git .github RELEASE.md .DS_Store
```

### 4. Create the ZIP archive

```bash
cd ..
zip -r SwagMigrationConnector.zip SwagMigrationConnector
```

### 5. Create a new tag on GitHub

In your working copy of the repository (not the cleaned release copy), create and push a tag matching the version in `plugin.xml`:

```bash
git tag <version> -m "vX.Y.Z"
git push origin --tags
```

### 6. Upload to the Shopware extension account

1. Log in to the [Shopware Account](https://account.shopware.com)
2. Navigate to **Extension Partner** > **Erweiterungen** > **Plugin-System** > **Shopware 5**
3. Search and select **SwagMigrationConnector**
4. Scroll down to the **Versionen** section and click on **Neue Version hochladen**
5. Follow the wizard and create a new version matching `plugin.xml`
6. Upload the `SwagMigrationConnector.zip`
7. Fill in the changelog (can be copied from `plugin.xml`)
8. Submit for review
9. Check if the new version is listed in the [Shopware Store](https://store.shopware.com/de/swag226607479310f/shopware-migration-connector.html)
