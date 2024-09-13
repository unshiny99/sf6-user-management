## Symfony 6 project : user management

System of management of users with roles and permissions

### Notes to test the project
1. Launch the local server : `symfony serve -d` or `symfony server:start`
2. Run `symfony console doctrine:fixtures:load`
3. Use login api : `https://localhost:8000/api/login`, using this payload :
```json
{
    "login": "admin", 
    "password": "admin"
}
```
4. Copy the returned token and use it in the Authorization request parameter of secured endpoints like so : `Authorization: Bearer <token>` \
5. API documentation is accessible at `https://127.0.0.1:8000/api/doc` \
You can then create your own user if you want, or do other actions.
6. Tests are runnable via `php bin/phpunit tests/Unit`

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
- Functional tests does not work as expected : they all return a 200 code instead of the correct information. I have tried to fix this issue, but didn't manage to it.
My supposition was that a configuration was missing. That is why global test currently don't work.
- Unit tests are implemented for the User entity.
Documentation : all the documentation (in code, Swagger and this README) is in English. If it's a problem for you, please contact me.

### Credits
Maxime Frémeaux @unshiny99