use admin;

db.createUser(
    {
        user: "punto",
        pwd: "jjK5AfeQ7RUvgSKbdzw9B85LFGQ5e6zXe8ujgR",
        roles: [
            "userAdminAnyDatabase",
            "dbAdminAnyDatabase",
            "readWriteAnyDatabase"
        ]
    }
);
