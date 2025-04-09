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
        host: "%env(NEXUS_TYPESENSE_HOST)%"
        prot: "%env(NEXUS_TYPESENSE_PROT)%"
        port: "%env(NEXUS_TYPESENSE_PORT)%"
        api_key: "%env(NEXUS_TYPESENSE_API_KEY)%"
    frontend:
        alias: "nexus--current"
        api_key: "nexus:search-key"
    authorization:
        policies:
            ROLE_USER: 'user.get("ROLE_DEVELOPER")'
```

| variable     | type   | content                                                             |
| ------------ | ------ | ------------------------------------------------------------------- |
| topics       | array  | strings are URLs to the `topic.metatdada.json` files of the apps    |
| **typsense** |        | Settings for the internal connction to the typesense server         |
| host         | string | name or ip of the typsense server to talk to                        |
| prot         | string | protocol to talk to the typesense server either 'http' or 'https'   |
| port         | int    | port of the typesense server to talk to                             |
| api_key      | string | typesense API key to create, query and delete typesense collections |
| **frontend** |        | Settings for the front end app                                      |
| alias        | string | name of the current collection to query via the typesense proxy     |
| api_key      | string | API key to query the current collection via the typesense proxy     |

## Automatic import into new collection

Run `bin/console dbp:relay:nexus:generate:activities` to create a new collection with currently available activities. If documents were imported, the alias is set to the latest import and obsolete collections are deleted.
