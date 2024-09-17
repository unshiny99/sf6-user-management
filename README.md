## Symfony 6 project : user management

System of management of users with roles and permissions

### Requirements
1. PHP 8
2. Composer
3. PostgresSQL
3. Symfony CLI
4. A REST client (like Postman)

### Notes to test the project
1. Launch the local server : `symfony server:start`
2. Run `composer install`
3. In the `.env`, change DATABASE_URL with a working local Postgres' DSN
4. Run `symfony console doctrine:migrations:migrate`
5. Run `symfony console doctrine:fixtures:load`
6. Use login api : `http://localhost:8000/api/login`, using this payload and credentials :
```json
{
    "login": "admin", 
    "password": "admin"
}
```
6. Copy the returned token and use it in the Authorization request parameter of secured endpoints like so : `Authorization: Bearer <token>` \
7. API documentation is accessible at `http://127.0.0.1:8000/api/doc` \
You can then create your own user if you want, or do other actions.
8. Tests are runnable via `php bin/phpunit` (please clean your test database after each global test)

### General comments on the processing
#### Behaviour
- If we update/delete a permission that was referenced in some roles, they will be consequently updated
- If we update/delete a role that was referenced in some users, they will be consequently updated

#### Middleware secutiry implementation 
- To see the restricted access API middleware, you need to have a user with 'ROLE_ADMIN' role
- To see a custom restricted action, try to delete an user. It should cause a forbidden access, except if one of the roles has 'PERMISSION_DELETE' permission.

#### Custom event listener
Both custom event and custom event listeners with mail sending have been implemented theoretically. 
However, send a mail required a SMTP server, or to use an API key from a provider. \
If you want to test this feature, use your SMTP settings in the MAILER_DSN var from the environment file.

#### Tests
- Functional tests have been implemented for UserController : they all return the wanted response code. These are the major features, but we could still add other tests later as we get the global code writing architecture.
- Unit tests are also implemented for the User entity.

#### Documentation
All the documentation (in code, Swagger and this README) is in English. If it's a problem for you, please contact me.

### Credits
Maxime Frémeaux @unshiny99