
You can use IIS to handle the Authentication and Authorization, and then use Docker to handle the assignment of the user to the appropriate website.

From here, you can restrict access to certain stuff on the website based on the user's role.


Logic goes:

1. User opens edge to the landing page (assigned from SSS GPO)
2. As soon as the page loads, it sends a verification request to the IIS server
3. The landing page binded to a user friendly name in IIS
4. IIS will then return the username of the user
5. php should be able to identify if the user is a staff or student based on the '.' on the username
6. php should then redirect the user to the appropriate website based on their role
7. php should also check if the user has used the landing page before and load the landing page layout according to what is saved in the database
8. if not, load the default landing page layout and create a new record in the database