// Expresiones regulares para el formulario de proveedores

const expresiones = {

    empresa: /^(?=.*[A-Za-zÁÉÍÓÚáéíóúÑñ])(?=.*\d)[A-Za-zÁÉÍÓÚáéíóúÑñ0-9#.,\-\s]{5,120}$/,

    nit: /^[0-9]{8,12}$/,

    nombre: /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/,

    apellido: /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/,

    telefono: /^[0-9]{10}$/,

    correo: /^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/,

    direccion: /^(?=.*[A-Za-zÁÉÍÓÚáéíóúÑñ])(?=.*\d)[A-Za-zÁÉÍÓÚáéíóúÑñ0-9#.,\-\s]{5,120}$/,

    descripcion: /^.{10,300}$/

};