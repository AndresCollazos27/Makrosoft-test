# Makrosoft Test - API de Contratos y Cuotas

Aplicación Symfony 7 (PHP 8.2) con Doctrine ORM para gestión de contratos y generación de cuotas con dos métodos de pago: **PayPal** (1% interés mensual, 2% tarifa) y **PayOnline** (2% interés mensual, 1% tarifa).

---

## Requisitos

- PHP 8.2+
- Composer
- Docker + Docker Compose
- Symfony CLI (opcional, recomendado)
- Postman (para probar los endpoints)

---

## Instalación

### 1. Clonar e instalar dependencias

```bash
git clone https://github.com/AndresCollazos27/Makrosoft-test.git
cd makrosoft_test
composer install
```

### 2. Levantar base de datos con Docker

```bash
docker compose up -d
```

La base de datos PostgreSQL 16 estará disponible en:
- **Host**: localhost
- **Puerto**: 55432
- **Usuario**: app
- **Contraseña**: app
- **Base de datos**: app

### 3. Ejecutar migraciones

```bash
php bin/console doctrine:migrations:migrate
```

### 4. Iniciar servidor

**Opción 1: Symfony CLI (recomendado)**
```bash
symfony server:start --port=8000
```

**Opción 2: Servidor PHP integrado**
```bash
php -S 127.0.0.1:8000 -t public
```

La aplicación estará disponible en: `http://127.0.0.1:8000`

---

## Endpoints de la API

### 1. Crear Contrato

**POST** `http://127.0.0.1:8000/api/contracts`

**Headers:**
```
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "numeroContrato": "CTR-2025-001",
  "fechaContrato": "2025-01-15",
  "valorTotalContrato": "12000.50",
  "metodoPagoSeleccionado": "paypal"
}
```

**Respuesta exitosa (201):**
```json
{
  "id": 1,
  "numeroContrato": "CTR-2025-001",
  "fechaContrato": "2025-01-15T00:00:00+00:00",
  "valorTotalContrato": "12000.50",
  "metodoPagoSeleccionado": "paypal"
}
```

**Validaciones:**
- `numeroContrato`: debe ser único
- `fechaContrato`: formato YYYY-MM-DD
- `valorTotalContrato`: número decimal positivo (string)
- `metodoPagoSeleccionado`: "paypal" o "payonline"

---

### 2. Proyectar Cuotas (Sin Guardar)

**GET** `http://127.0.0.1:8000/api/contracts/1/installments/projection?months=12`

**Parámetros de URL:**
- `months`: Número de cuotas (entero positivo)

**Respuesta (200):**
```json
{
  "contractId": 1,
  "numeroContrato": "CTR-2025-001",
  "valorTotalContrato": "12000.50",
  "metodoPago": "paypal",
  "numeroCuotas": 12,
  "cuotas": [
    {
      "numeroCuota": 1,
      "fechaVencimiento": "2025-02-15T00:00:00+00:00",
      "montoBase": "1000.04",
      "interes": "120.01",
      "tarifaAdministracion": "22.40",
      "valorCuota": "1142.45"
    },
    {
      "numeroCuota": 2,
      "fechaVencimiento": "2025-03-15T00:00:00+00:00",
      "montoBase": "1000.04",
      "interes": "110.01",
      "tarifaAdministracion": "22.20",
      "valorCuota": "1132.25"
    }
    // ... más cuotas
  ]
}
```

---

### 3. Guardar Cuotas en Base de Datos

**POST** `http://127.0.0.1:8000/api/contracts/1/installments?months=12`

**Parámetros de URL:**
- `months`: Número de cuotas (entero positivo)

**Respuesta (200):**
```json
{
  "message": "Cuotas generadas y guardadas exitosamente",
  "contractId": 1,
  "numeroCuotas": 12
}
```

**Nota:** Esta operación es idempotente. Si ya existen cuotas para el contrato, se eliminarán y reemplazarán con las nuevas.

---

## Fórmulas de Cálculo

### PayPal (código: `paypal`)
- Interés mensual: **1%**
- Tarifa de administración: **2%**

### PayOnline (código: `payonline`)
- Interés mensual: **2%**
- Tarifa de administración: **1%**

### Cálculo de cada cuota:

Para la cuota `i` de `N` cuotas totales:

1. **Monto base**: `base = valorTotal / N`
2. **Saldo pendiente**: `saldo = valorTotal - (base × (i - 1))`
3. **Interés**: `interes = saldo × tasaInteresMensual`
4. **Subtotal**: `subtotal = base + interes`
5. **Tarifa**: `tarifa = subtotal × tasaTarifa`
6. **Valor cuota**: `valorCuota = subtotal + tarifa`

Todos los cálculos se realizan con precisión decimal (BCMath, escala 2).

---

## Estructura del Proyecto

```
src/
├── Controller/
│   ├── ContractController.php              # POST /api/contracts
│   └── ContractInstallmentsController.php  # GET/POST /api/contracts/{id}/installments
├── Domain/
│   └── Payment/
│       ├── PaymentMethodCalculatorInterface.php
│       ├── AbstractPaymentCalculator.php
│       ├── PaypalCalculator.php            # 1% interés, 2% tarifa
│       ├── PayOnlineCalculator.php         # 2% interés, 1% tarifa
│       └── PaymentCalculatorResolver.php   # Factory
├── Entity/
│   ├── Contract.php                        # Entidad principal
│   └── Installment.php                     # Cuotas (OneToMany)
├── Repository/
│   ├── ContractRepository.php
│   └── InstallmentRepository.php
└── Service/
    └── ContractInstallmentService.php      # Lógica de negocio
```

---

## Probar con Postman

### Colección de Pruebas

#### Test 1: Crear contrato con PayPal
1. Método: **POST**
2. URL: `http://127.0.0.1:8000/api/contracts`
3. Headers: `Content-Type: application/json`
4. Body:
```json
{
  "numeroContrato": "CTR-001",
  "fechaContrato": "2025-01-20",
  "valorTotalContrato": "10000.00",
  "metodoPagoSeleccionado": "paypal"
}
```

#### Test 2: Proyectar cuotas (sin guardar)
1. Método: **GET**
2. URL: `http://127.0.0.1:8000/api/contracts/1/installments/projection?months=6`
3. Headers: ninguno necesario

#### Test 3: Guardar cuotas en BD
1. Método: **POST**
2. URL: `http://127.0.0.1:8000/api/contracts/1/installments?months=6`
3. Headers: ninguno necesario

#### Test 4: Crear contrato con PayOnline
1. Método: **POST**
2. URL: `http://127.0.0.1:8000/api/contracts`
3. Headers: `Content-Type: application/json`
4. Body:
```json
{
  "numeroContrato": "CTR-002",
  "fechaContrato": "2025-01-22",
  "valorTotalContrato": "15000.00",
  "metodoPagoSeleccionado": "payonline"
}
```

---

## Verificar Base de Datos

Conectar con **DataGrip** o **pgAdmin**:

- **Host**: localhost
- **Puerto**: 55432
- **Usuario**: app
- **Contraseña**: app
- **Base de datos**: app

### Tablas:
- `contract`: almacena contratos
- `installment`: almacena cuotas
- `doctrine_migration_versions`: control de migraciones

---

## Comandos Útiles

### Docker
```bash
# Iniciar contenedores
docker compose up -d

# Ver logs
docker compose logs -f

# Detener contenedores
docker compose down

# Eliminar volúmenes (resetear BD)
docker compose down -v
```

### Symfony
```bash
# Ver rutas disponibles
php bin/console debug:router

# Limpiar caché
php bin/console cache:clear

# Crear nueva migración
php bin/console make:migration

# Ejecutar migraciones
php bin/console doctrine:migrations:migrate

# Ver estado de migraciones
php bin/console doctrine:migrations:status
```

---

## Tecnologías

- **Symfony 7.4** (PHP 8.2)
- **Doctrine ORM 3.6**
- **PostgreSQL 16**
- **BCMath** (cálculos de precisión decimal)
- **Docker Compose**
- **Strategy Pattern** (métodos de pago)

---
