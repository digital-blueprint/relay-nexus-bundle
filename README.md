# DbpRelayNexusBundle

[GitHub](https://github.com/relay-nexus-bundle) |
[Packagist](https://packagist.org/packages/dbp/relay-nexus-bundle) |
[Frontend Application](https://github.com/digital-blueprint/nexus-app)

The Nexus bundle provides an API for providing a desktop application merging
multiple frontend activities.

There is a corresponding frontend application that uses this API at [Nexus Frontend Application](https://github.com/digital-blueprint/nexus-app).

## Bundle installation

You can install the bundle directly from [packagist.org](https://packagist.org/packages/dbp/relay-nexus-bundle).

```bash
composer require dbp/relay-nexus-bundle
```

## Bundle configuration

Add this file `dbp_relay_nexus.yaml` to your `config/packages/` directory, e.g.:

```yaml
dbp_relay_nexus:
    topics:
        - "https://server01.org/app/app.topic.metadata.json"
        - "https://server02.org/app/app.topic.metadata.json"
        - "https://server03.org/app/app.topic.metadata.json"
    typesense:
        api_url: "%env(NEXUS_TYPESENSE_API_URL)%"
        api_key: "%env(NEXUS_TYPESENSE_API_KEY)%"
    authorization:
        roles:
            ROLE_USER: 'user.get("ROLE_DEVELOPER")'
```

| variable     | type   | content                                                             |
|--------------|--------|---------------------------------------------------------------------|
| topics       | array  | strings are URLs to the `topic.metatdada.json` files of the apps    |
| **typsense** |        | Settings for the internal connction to the typesense server         |
| api_url      | string | Typesense API URL of the internal typesense server                  |
| api_key      | string | typesense API key to create, query and delete typesense collections |

## Automatic import into new collection

Run `bin/console dbp:relay:nexus:generate:activities` to create a new collection with currently available activities. If documents were imported, the alias is set to the latest import and obsolete collections are deleted.
