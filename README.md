# Restresources component

## Information

This component has for purpose to provide a simple REST API with no development.
 
## How to use
  - Your entity repository must implement our `ResourceRepositoryInterface`
  - Import our controller on your routing file 
  ``` 
    restresources_api:
       resource: ../../vendor/numerique1/restresources/Numerique1/Components/Restresources/Controller/
       type: annotation
  ```
  - Create your *.resource.yml `ex. users.resource.yml` file with the `restresources:file:create` command

## More

The API security is handled by the Symfony voters we use the following attributes : 
 - VIEW_LIST
 - VIEW
 - CREATE
 - CREATE_{$childResource} __(this one is special see. Child resources)__
 - UPDATE
 - DELETE
 ------
 ------
 - Each `GET` and `CGET` actions has a `_group` parameters which must match your serializer "Groups" annotation, the default used is `minimal`.
 - `EntityMetadataFilterTrait` allow you to filter on each entity fields of the resource
 
##Child resources

Sorry.