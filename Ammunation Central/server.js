const express = require("express");
const session = require("express-session");
const bcrypt = require("bcrypt");
const path = require("path");

const db = require("./database");

const app = express();

const PORT = 3000;


/* -------------------------
   CONFIGURACIÓN
------------------------- */

app.use(express.json());
app.use(express.urlencoded({ extended: true }));

app.use(
    session({
        secret: "clave-secreta-comercio-juego",
        resave: false,
        saveUninitialized: false,
        cookie: {
            maxAge: 1000 * 60 * 60 * 8
        }
    })
);


/* -------------------------
   ARCHIVOS PÚBLICOS
------------------------- */

app.use(express.static(path.join(__dirname, "public")));


/* -------------------------
   LOGIN
------------------------- */

app.post("/api/login", async (req, res) => {

    const { usuario, password } = req.body;

    if (!usuario || !password) {

        return res.status(400).json({
            error: "Introduce usuario y contraseña."
        });

    }


    const empleado = db
        .prepare(
            "SELECT * FROM empleados WHERE usuario = ?"
        )
        .get(usuario);


    if (!empleado) {

        return res.status(401).json({
            error: "Usuario o contraseña incorrectos."
        });

    }


    const correcto = await bcrypt.compare(
        password,
        empleado.password
    );


    if (!correcto) {

        return res.status(401).json({
            error: "Usuario o contraseña incorrectos."
        });

    }


    req.session.empleado = {
        id: empleado.id,
        usuario: empleado.usuario,
        nombre: empleado.nombre,
        rango: empleado.rango
    };


    res.json({
        correcto: true
    });

});


/* -------------------------
   COMPROBAR SESIÓN
------------------------- */

app.get("/api/sesion", (req, res) => {

    if (!req.session.empleado) {

        return res.status(401).json({
            autenticado: false
        });

    }


    res.json({
        autenticado: true,
        empleado: req.session.empleado
    });

});


/* -------------------------
   CERRAR SESIÓN
------------------------- */

app.post("/api/logout", (req, res) => {

    req.session.destroy(() => {

        res.json({
            correcto: true
        });

    });

});


/* -------------------------
   MIDDLEWARE EMPLEADO
------------------------- */

function comprobarEmpleado(req, res, next) {

    if (!req.session.empleado) {

        return res.status(401).json({
            error: "No tienes una sesión activa."
        });

    }

    next();

}


/* -------------------------
   PRODUCTOS
------------------------- */

app.get("/api/productos", comprobarEmpleado, (req, res) => {

    const productos = db
        .prepare(
            "SELECT * FROM productos ORDER BY id DESC"
        )
        .all();

    res.json(productos);

});


app.post("/api/productos", comprobarEmpleado, (req, res) => {

    const {
        nombre,
        categoria,
        precio,
        stock
    } = req.body;


    if (!nombre) {

        return res.status(400).json({
            error: "El producto necesita un nombre."
        });

    }


    const resultado = db
        .prepare(
            `
            INSERT INTO productos
            (nombre, categoria, precio, stock)
            VALUES (?, ?, ?, ?)
            `
        )
        .run(
            nombre,
            categoria || "",
            Number(precio) || 0,
            Number(stock) || 0
        );


    res.json({
        correcto: true,
        id: resultado.lastInsertRowid
    });

});


app.delete("/api/productos/:id", comprobarEmpleado, (req, res) => {

    db
        .prepare(
            "DELETE FROM productos WHERE id = ?"
        )
        .run(req.params.id);


    res.json({
        correcto: true
    });

});


/* -------------------------
   STOCK
------------------------- */

app.patch("/api/stock/:id", comprobarEmpleado, (req, res) => {

    const cantidad = Number(req.body.cantidad);


    if (Number.isNaN(cantidad)) {

        return res.status(400).json({
            error: "Cantidad incorrecta."
        });

    }


    db
        .prepare(
            `
            UPDATE productos
            SET stock = stock + ?
            WHERE id = ?
            `
        )
        .run(
            cantidad,
            req.params.id
        );


    res.json({
        correcto: true
    });

});


/* -------------------------
   FORMULARIOS
------------------------- */

app.get("/api/formularios", comprobarEmpleado, (req, res) => {

    const formularios = db
        .prepare(
            "SELECT * FROM formularios ORDER BY id DESC"
        )
        .all();

    res.json(formularios);

});


app.post("/api/formularios", comprobarEmpleado, (req, res) => {

    const {
        tipo,
        descripcion
    } = req.body;


    db
        .prepare(
            `
            INSERT INTO formularios
            (tipo, descripcion, empleado)
            VALUES (?, ?, ?)
            `
        )
        .run(
            tipo || "General",
            descripcion || "",
            req.session.empleado.usuario
        );


    res.json({
        correcto: true
    });

});


/* -------------------------
   EMPLEADOS
------------------------- */

app.get("/api/empleados", comprobarEmpleado, (req, res) => {

    const empleados = db
        .prepare(
            `
            SELECT id, usuario, nombre, rango
            FROM empleados
            ORDER BY id DESC
            `
        )
        .all();

    res.json(empleados);

});


/* -------------------------
   INICIAR SERVIDOR
------------------------- */

app.listen(PORT, () => {

    console.log("");
    console.log("=================================");
    console.log("   COMERCIO WEB");
    console.log("=================================");
    console.log("");
    console.log(`Servidor: http://localhost:${PORT}`);
    console.log("");

});