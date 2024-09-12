## Symfony 7 project : user management

System of management of users with roles and permissions

### Notes to test the project
1. Launch the local server : `symfony serve -d` or `symfony server:start`
2. Use login api : `https://localhost:8000/api/users`
3. Copy the returned token and use it in Authorization parameter of secured endpoints like so : `Authorization: Bearer <token>`

### General comments on the processing
- If we update/delete a permission that was referenced in some roles, they will be consequently updated
- If we update/delete a role that was referenced in some users, they will be consequently updated

## TODO temp
- Valider les données en entrée
- Middleware pour vérifier les routes ? 
    Implémenter un système de middleware pour vérifier les permissions sur certaines routes.
- Test unitaires / intégration ?
- Swagger ?