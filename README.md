# *Plant Care Diary — application/project description*

The application is a personal plant care tracker. The core idea is simple: a user registers,
adds their houseplants to their account, and keeps a diary of every time they water, fertilise,
or repot them. Instead of trying to remember when you last watered your monstera (a succulent, in my case), 
one can open the app and it'd tell them everything.

## User's perspective

A guest visiting the site can browse a public catalogue of plant types (succulents, tropical plants,
herbs, etc. – hopes ad prayers I find an actual database )and see general care information 
— but they can't do anything personal until they register.

Once logged in, a user can add their own plants to their account, giving each one a name 
(e.g. "my big monstera by the window") and linking it to a plant type from the catalogue. 
Every time they water or fertilise a plant, they add a care log entry — a short note with a timestamp. 
Over time this builds up a history: "I watered this plant on the 1st, 8th, and 15th — looks like I'm doing
it every 7 days." 

~~Fully depends on one's pattern recognition, now that I think of it.~~

*! Search and filtering*

Users can search and filter in two places. First, in the plant catalogue — filtering by type
(only show succulents), or searching by name. Second, in their own plant list and care history, such as 
filtering care logs by date range ("my divine Self wishes to unseal the forgotten scrolls dated March") 
or by action type ("show me only watering entries, *for I am no peasant and posses treasurable dihydrogen 
monoxide!*"). This is especially useful once someone has 10+ plants with dozens of log entries and way 
too little space for more mental load.

## The admin's role

The admin *never* interacts with anyone's plants. Their job is purely system management
— they maintain the plant types catalogue (adding new types like "cactus" or "bonsai",
deactivating outdated ones), and they manage user accounts (resetting passwords, assigning roles,
removing accounts if needed).

**The roles in short:**
- **Guest** — browses the public plant catalogue, registers or logs in;
- **User** — manages their own plants and care diary;
- **Admin** — manages plant types (database) and user accounts. Doesn't even touch the creation of one's
personal diary enties.


  
*So I don't forget:* 

| Action | Guest | User | Admin |
|---|---|---|---|
| Browse public plant catalogue | O | O | O |
| Register | O | X | X |
| Add own plants | X | O | O |
| Log watering / care entries | X | O | O |
| View own plant diary | X | O | O |
| Manage plant types (catalogue) | X | X | O |
| Manage users & roles | X | X | O |
| Search & filter everything | O | O | O |

ATTENTION! 

Having considered Admin's hypothetical need for a personal garden, they now can have plans of their own too!



# Technical side

## Stack

| Layer | What |
|---|---|
| Server | XAMPP on macOS |
| Backend | PHP 8.2 / Laravel 12 |
| DB access | [Medoo](https://medoo.in/) | 
| Database | MySQL 8 (XAMPP) |
| Sessions | file-backed (`SESSION_DRIVER=file`) |
| Hashing | bcrypt |
| Frontend | HTML5 UP *Twenty* + Blade |

## Database

The canonical schema lives in [`plant_diary_ddl.sql`](plant_diary_ddl.sql), written for Oracle.

The **running** database is a port of that DDL to MySQL — the exact same tables and logic, accommodated for 
MySQL. 

Two Version because the Oracle one is the design artefact graded by the prof (and it was the first thing 
that came to mind), and the MySQL one is the one I can actually run without Docker, an Oracle account,
and three hours of my life I'd never get back.


## Todo list 
~~aka someone give me patience~~
- [x] Public plant-type catalogue (read-only, visible to guests)
- [x] User: add / ~~edit / delete their own plants~~
- [x] User: log a care entry, view their diary, filter by date / action
- [x] Admin UI: CRUD for plant types, user / role management (no more phpMyAdmin SQL gymnastics)
- [x] Admin *working* UI: connection to the database, actual user manipulation
- [x] Search & filter on both the public catalogue and one's own diary
- [x] Cosmetic: a home page that doesn't look like a placeholder
- [x] Pagination
- [x] Ajax
- [ ] Optional: a prettier filtrering
