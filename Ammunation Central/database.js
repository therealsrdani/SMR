const Database = require("better-sqlite3");
const bcrypt = require("bcrypt");
const fs = require("fs");
const path = require("path");


const dataFolder = path.join(__dirname, "data");


if (!fs.existsSync(dataFolder)) {
    fs.mkdirSync(dataFolder);
}


const db = new Database(
    path.join(dataFolder, "comercio.db")
);


/* -------------------------
   TABLA EMPLEADOS
------------------------- */

db.prepare(`
    CREATE TABLE IF NOT EXISTS empleados (

        id INTEGER PRIMARY KEY AUTOINCREMENT,

        usuario TEXT UNIQUE NOT NULL,

        password TEXT NOT NULL,

        nombre TEXT NOT NULL,

        rango TEXT NOT NULL

    )
`).run();


/* -------------------------
   TABLA PRODUCTOS
------------------------- */

db.prepare(`
    CREATE TABLE IF NOT EXISTS productos (

        id INTEGER PRIMARY KEY AUTOINCREMENT,

        nombre TEXT NOT NULL,

        categoria TEXT,

        precio REAL DEFAULT 0,

        stock INTEGER DEFAULT 0

    )
`).run();


/* -------------------------
   TABLA FORMULARIOS
------------------------- */

db.prepare(`
    CREATE TABLE IF NOT EXISTS formularios (

        id INTEGER PRIMARY KEY AUTOINCREMENT,

        tipo TEXT,

        descripcion TEXT,

        empleado TEXT,

        fecha DATETIME DEFAULT CURRENT_TIMESTAMP

    )
`).run();


/* -------------------------
   EMPLEADO INICIAL
------------------------- */

const empleadoExistente = db
    .prepare(
        "SELECT id FROM empleados WHERE usuario = ?"
    )
    .get("admin");


if (!empleadoExistente) {

    const password = bcrypt.hashSync(
        "admin123",
        10
    );


    db.prepare(`
        INSERT INTO empleados
        (usuario, password, nombre, rango)
        VALUES (?, ?, ?, ?)
    `).run(
        "admin",
        password,
        "Administrador",
        "Gerente"
    );

}


/* -------------------------
   PRODUCTOS DE EJEMPLO
------------------------- */

const cantidadProductos = db
    .prepare(
        "SELECT COUNT(*) AS cantidad FROM productos"
    )
    .get();


if (cantidadProductos.cantidad === 0) {

    const insertar = db.prepare(`
        INSERT INTO productos
        (nombre, categoria, precio, stock)
        VALUES (?, ?, ?, ?)
    `);


    insertar.run(
        "Producto 1",
        "General",
        100,
        25
    );


    insertar.run(
        "Producto 2",
        "General",
        50,
        10
    );


    insertar.run(
        "Producto 3",
        "General",
        75,
        5
    );

}


module.exports = db;