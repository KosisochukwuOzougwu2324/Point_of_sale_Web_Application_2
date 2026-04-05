### ProjectURL
https://github.com/KosisochukwuOzougwu2324/Point_of_sale_Web_Application_2

### Login Credentials For Admin
Email - admin@pos.com
Password - 123456

### Login Credentials for User
Email - emjay3669@gmail.com
Password - 123456

### MVC
The model-view-controller pattern was adhered to.

### What The Application Does
The Public product catalogue enable users with search and category filtering.
Customers can register, login,check the shopping cart, and checkout.
Customers have access to delivery (doorstep) or Pickup (store) options.
Online payment for users are able through via Stripe or Pay on Delivery/Pickup.
Thi spaplication has In-store POS for staff to process sales using product codes.
The Admin dashboard displays sales analytics for (today, week, month, year).
The User management with role-based access (Admin, Editor, User, Customer, Driver).
Order management with status tracking and driver assignment.

### SOC
The frontend and backend can be developed and deployed independently
The backend is a pure API — it can serve web apps, mobile apps, or any other client
Communication is made stateless via JWT tokens, not PHP sessions

### Authentication (JWT)
The application uses JSON Web Tokens for stateless authentication, replacing PHP sessions.
JWT works with the Login, Storage, validation and Role-Checking for the application.
