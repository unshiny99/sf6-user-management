## Symfony 7 project : user management

System of management of users with roles and permissions

### Notes to test the project
1. Launch the local server : `symfony serve -d` or `symfony server:start`
2. Run `symfony console doctrine:fixtures:load`
2. Use login api : `https://localhost:8000/api/users`
3. Copy the returned token and use it in Authorization parameter of secured endpoints like so : `Authorization: Bearer <token>`
4. API documentation is accessible at `https://127.0.0.1:8000/api/doc`

### General comments on the processing
- If we update/delete a permission that was referenced in some roles, they will be consequently updated
- If we update/delete a role that was referenced in some users, they will be consequently updated

Middleware secutiry implementation : 
- To see the restricted access API middleware, you need to have a user with 'ROLE_ADMIN' role
- To see a custom restricted action, try to delete an user. It should cause a forbidden access, except if one of the roles has 'PERMISSION_DELETE' permission.

## TODO temp
- Swagger : coder les docs pour avoir les noms de fcts, descriptions...
- Test unitaires / intégration ?