# SDK para Facturación de Servicios M2M/IoT en OMVs

![PHP](https://img.shields.io/badge/PHP-8.0+-blueviolet) ![License](https://img.shields.io/badge/License-MIT-green) ![Tests](https://img.shields.io/badge/Tests-Passing-brightgreen) ![Version](https://img.shields.io/badge/Version-1.0-blue)

Bienvenido al **SDK de Facturación M2M/IoT**, una solución de código abierto desarrollada en PHP para optimizar la facturación de servicios Machine-to-Machine (M2M) e Internet de las Cosas (IoT) en Operadores Móviles Virtuales (OMVs). Este proyecto, creado como parte de un Trabajo Fin de Grado en Ingeniería Informática, aborda los desafíos de la heterogeneidad de formatos de registros UDR/CDR y la necesidad de interoperabilidad, ofreciendo una herramienta flexible, escalable y adaptable a diversos modelos de negocio.

## 📋 Descripción

Este SDK permite a los OMVs procesar grandes volúmenes de registros UDR para generar facturas precisas y personalizadas. Soporta bases de datos relacionales (MySQL, PostgreSQL, SQL Server, Oracle) y no relacionales (MongoDB), con una arquitectura modular y configurable mediante ficheros YAML.

### ✨ Características Principales
- **Flexibilidad**: Adaptable a diferentes OMVs.
- **Escalabilidad**: Diseñado para entornos locales, servidores dedicados o clústeres de alta disponibilidad.
- **Código Abierto**: Basado en componentes open-source, sin dependencia de soluciones comerciales.
- **Interoperabilidad**: Soporte para almacenamiento en la nube (AWS S3, Google Cloud Storage) y generación de informes de pre-facturas en CSV.

## 🚀 Requisitos

Antes de instalar el SDK, asegúrate de cumplir con los siguientes requisitos:

### Software
- **PHP**: Versión 8.0 o superior.
- **Composer**: Versión 2.0 o superior.
- **Bases de Datos**:
  - Relacionales: MySQL (8.0+), PostgreSQL (13.0+), Microsoft SQL Server (2019+), Oracle (19c+).
  - NoSQL: MongoDB (5.0+).
- **Sistema Operativo**: Linux (recomendado), Windows o macOS.


### Extensiones de PHP
Habilita las siguientes extensiones en tu instalación de PHP:
- `pdo_mysql`, `pdo_pgsql`, `pdo_sqlsrv`, `pdo_oci` (según la base de datos relacional).
- `mongodb` (para MongoDB).
- `mbstring`, `json`, `fileinfo`.

### Dependencias Adicionales
- **Microsoft ODBC Driver for SQL Server**: Necesario para SQL Server (versión 17 o superior).
- **Oracle Instant Client**: Requerido para Oracle Database (versión 19.0 o superior).

## 📦 Instalación

Sigue estos pasos para instalar y configurar el SDK en tu entorno:

### 1. Clonar el Repositorio
Clona el repositorio en tu máquina local:
```bash
git clone https://github.com/tu-usuario/tfg-m2m-billing-sdk.git
cd tfg-m2m-billing-sdk
```

### 2. Instalar y Configurar Composer para Gestionar Dependencias
El SDK utiliza **Composer**, el gestor de dependencias de PHP, para instalar las librerías necesarias definidas en `composer.json`. Sigue estos pasos para instalar Composer y las dependencias del proyecto:

#### a. Verificar o Instalar Composer
Asegúrate de tener Composer instalado (versión 2.0 o superior). Para verificarlo, ejecuta:
```bash
composer --version
```
Si Composer no está instalado o la versión es antigua, instálalo según tu sistema operativo:

- **Linux**:
  ```bash
  sudo apt update
  sudo apt install php-cli unzip -y
  curl -sS https://getcomposer.org/installer | php
  sudo mv composer.phar /usr/local/bin/composer
  composer --version
  ```
  Esto instala Composer globalmente y muestra la versión instalada.



#### b. Instalar las Dependencias del Proyecto
Desde el directorio raíz del proyecto (`tfg-m2m-billing-sdk`), ejecuta:
```bash
composer install
```
Este comando:
- Lee el archivo `composer.json`.
- Descarga e instala las siguientes librerías en la carpeta `vendor`:
  - `noodlehaus/config`: Para gestionar ficheros de configuración YAML.
  - `symfony/yaml`: Para procesar archivos YAML.
  - `mongodb/mongodb`: Para conectar con MongoDB.
  - `illuminate/database`: Eloquent ORM para bases de datos relacionales.
  - `league/flysystem`: Abstracción de sistemas de ficheros (local, AWS S3, Google Cloud Storage).
  - `fpdf/fpdf`: Generación de facturas en formato PDF.
  - `monolog/monolog`: Gestión de logs y auditoría.
  - `ramsey/uuid`: Generación de identificadores únicos.
- Genera el archivo `vendor/autoload.php` para cargar automáticamente las dependencias.

#### c. Actualizar Dependencias (Opcional)
Si deseas asegurarte de tener las últimas versiones compatibles de las dependencias, ejecuta:
```bash
composer update
```
Nota: Usa `composer update` con precaución, ya que puede introducir cambios que requieran ajustes en el código.



### 3. Configurar el Entorno
1. **Copiar el archivo de configuración de ejemplo**:
   ```bash
   cp config/config.yml.example config/config.yml
   ```
2. **Editar `config.yml`**:
   Configura las conexiones a las bases de datos, almacenamiento en la nube y la lógica de facturación específica del OMV. Ejemplo:
   ```yaml
   database:
     relational:
       driver: mysql
       host: localhost
       port: 3306
       database: billing_db
       username: user
       password: pass
     nosql:
       driver: mongodb
       uri: mongodb://localhost:27017
       database: cdr_db
       collection: cdr_collection
   storage:
     driver: s3
     key: <aws_access_key>
     secret: <aws_secret_key>
     region: eu-west-1
     bucket: invoices-bucket
   invoice_structure:
     HDR_BILL_CYCLE_PERIOD:
       type: concat
       values: ["year", "month"]
     HDRCUSTOMER_ID:
       type: method
       name: getCustomerAccountID
       args: ["idCustomer"]
     # Más configuraciones según Anexo C
   ```

3. **Crear las tablas de la base de datos relacional**:
   Asegúrate de que las tablas (`Customers`, `Invoices`, `Invoice_details`) estén creadas conforme a los modelos en `src/Models`. Por ejemplo, para MySQL:
   ```sql
   CREATE DATABASE billing_db;
   -- Ejecutar scripts SQL desde src/Models
   ```

4. **Configurar MongoDB**:
   Crea la colección especificada para los registros UDR (e.g., `cdr_collection`):
   ```bash
   mongo cdr_db --eval 'db.createCollection("cdr_collection")'
   ```

5. **Configurar SQL Server o Oracle (si aplica)**:
   - Para SQL Server, instala el Microsoft ODBC Driver:
     ```bash
     # En Ubuntu
     curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add -
     curl https://packages.microsoft.com/config/ubuntu/20.04/prod.list > /etc/apt/sources.list.d/mssql-release.list
     sudo apt-get update
     sudo ACCEPT_EULA=Y apt-get install -y msodbcsql17
     ```
   - Para Oracle, instala Oracle Instant Client y configura las variables de entorno:
     ```bash
     export LD_LIBRARY_PATH=/path/to/oracle/instantclient_19_0:$LD_LIBRARY_PATH
     ```




## 📜 Licencia

Licenciado bajo la [Licencia MIT](LICENSE).

## 🙌 Agradecimientos

A **Víctor Daniel Díaz Suárez**, director del TFG, y a la **Universidad Internacional de La Rioja (UNIR)** por su apoyo.

---

⭐ **¡Dale una estrella al repositorio si te gusta el proyecto!** ⭐
