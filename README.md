# SDK para Facturación de Servicios M2M/IoT en OMVs

![PHP](https://img.shields.io/badge/PHP-8.0+-blueviolet) ![License](https://img.shields.io/badge/License-MIT-green) ![Tests](https://img.shields.io/badge/Tests-Passing-brightgreen) ![Version](https://img.shields.io/badge/Version-1.0-blue)

Bienvenido al **SDK de Facturación M2M/IoT**, una solución de código abierto desarrollada en PHP para optimizar la facturación de servicios Machine-to-Machine (M2M) e Internet de las Cosas (IoT) en Operadores Móviles Virtuales (OMVs). Este proyecto, creado como parte de un Trabajo Fin de Grado en Ingeniería Informática, aborda los desafíos de la heterogeneidad de formatos de registros UDR/CDR y la necesidad de interoperabilidad, ofreciendo una herramienta flexible, escalable y adaptable a diversos modelos de negocio.

## 📋 Descripción

Este SDK permite a los OMVs procesar grandes volúmenes de registros UDR (hasta **6.3 millones** con un rendimiento de hasta **40,715 registros/segundo**) para generar facturas precisas y personalizadas. Soporta bases de datos relacionales (MySQL, PostgreSQL, SQL Server, Oracle) y no relacionales (MongoDB), con una arquitectura modular y configurable mediante ficheros YAML.

### ✨ Características Principales
- **Flexibilidad**: Compatible con múltiples formatos de UDR/CDR y adaptable a diferentes OMVs.
- **Escalabilidad**: Diseñado para entornos locales, servidores dedicados o clústeres de alta disponibilidad.
- **Código Abierto**: Basado en componentes open-source, sin dependencia de soluciones comerciales.
- **Alto Rendimiento**: Validado en un entorno real con datos de mayo de 2025.
- **Interoperabilidad**: Soporte para almacenamiento en la nube (AWS S3, Google Cloud Storage) y generación de facturas en PDF.

## 🚀 Requisitos

Antes de instalar el SDK, asegúrate de cumplir con los siguientes requisitos:

### Software
- **PHP**: Versión 8.0 o superior.
- **Composer**: Versión 2.0 o superior.
- **Bases de Datos**:
  - Relacionales: MySQL (8.0+), PostgreSQL (13.0+), Microsoft SQL Server (2019+), Oracle (19c+).
  - NoSQL: MongoDB (5.0+).
- **Sistema Operativo**: Linux (recomendado) o Windows.
- **Servidor Web (opcional)**: Nginx (1.18+) o Apache (2.4+) con PHP-FPM para despliegues con API REST.

### Extensiones de PHP
Asegúrate de tener instaladas las siguientes extensiones:
- `pdo_mysql`, `pdo_pgsql`, `pdo_sqlsrv`, `pdo_oci` (según la base de datos relacional).
- `mongodb` (para MongoDB).
- `mbstring`, `json`, `fileinfo`.

### Dependencias Adicionales
- **Microsoft ODBC Driver for SQL Server**: Requerido para SQL Server (versión 17 o superior).
- **Oracle Instant Client**: Necesario para Oracle Database (versión 19.0 o superior).

## 📦 Instalación

Sigue estos pasos para instalar y configurar el SDK en tu entorno:

### 1. Clonar el Repositorio
```bash
git clone https://github.com/tu-usuario/tfg-m2m-billing-sdk.git
cd tfg-m2m-billing-sdk
