# Notas de Lanzamiento

**Kavac** es un **sistema de planificación y gestión de recursos organizacionales**, que abarca diferentes aristas como los recursos financieros, económicos, bienes, recursos materiales y humanos, y además de incluir herramientas para el manejo de las relaciones con los proveedores y los clientes.

Este sistema integra y automatiza los procesos administrativos, con elementos de seguridad de la información como la firma electrónica, **garantizando la autenticidad e integridad de cada documento y archivo generado por el sistema.**

**Kavac** , es un sistema intuitivo, que brinda **elementos gráficos, analíticos que garantiza el manejo eficiente de los recursos de la organización**, además de establecer canales de información en tiempo real acerca de la situación actual de cada uno de los departamentos, suministrando información útil y oportuna para la toma de decisiones, para el logro de los objetivos.

A continuación se describe la liberación de las versiones

## V1.0.5 (27/08/2024)

Ajustes y correcciones en:

 * Aplicación base
 * Módulos:
   * Talento Humano
   * Presupuesto
   * Finanzas
   * Contabilidad
   * Bienes
   * Almacén

Actualización de documentación y requerimientos en archivo README.md

Actualización de documentación de usuario

Actualización de documentación de sistema

Incorporación de nuevas funcionalidades en los módulos de:

 * Presupuesto:
   * Descarga de la formulación de presupuesto en formato de hoja de cálculo
   * Niveles de aprobaciónen la formulación de presupuesto
   * Solicitud de disponibilidad presupuestaria para la ejecución en otros procesos del sistema
   * Permitir la modificación de una disponibilidad presupuestaria luego de haber sido emitida
   * Reporte de acumulado por cuentas
   * Reporte de consolidado
   * Campo de código ONAPRE opcional y configurable desde las variables de entorno
   * El monto total de la formulación no puede ser editado
   * Agregado instructivo para la carga masiva de datos
   * Ajustes en el reporte de Mayor Analítico
   * Agregada opción paracargar documento que avale las modificaciones presupuestarias
   * Reporte de modificaciones presupuestarias
   * Se agregó el campo de monedaen las modificaciones presupuestarias
   * Generación de reportes en diferentes monedas
   * Ejecución presupuestaria en la misma moneda en la que fue formulada
   * Notificación al usuario en la ejecución presupuestaria sin disponibilidad
   * Campo opcional ***tipo de financiamiento*** en formulación y modificaciones presupuestarias

 * Finanzas
   * Modificaciones en el formulario de orden de pago, se sustituye ***cotizaciones*** por ***orden de compra***
   * Modificación en orden de pago: los campos de forma de pago, entidad bancaria y cuenta bancaria se mueven al formulario de ejecución de pago
   * Optimización en el reporte de orden de pago
   * Carga automática del concepto en las órdenes de pago
   * Se agrega la conciliación bancaria

 * Talento Humano
   * Generación de reportes en formato de hoja de cálculo
   * Consulta y generación de recibos de pago por ntrabajador, nómina y/o período de nómina
   * Modificación en nombre de formulario de tipos de excepciones, ahora se llama categorías de la hoja de tiempo
   * Modificación en nombre de formulario detipos de pago, ahora se llama tipos de nómina
   * Se establece la edad mínima para trabajar en 14 años según lo establece la Ley Orgánica del Trabajo, Tabajadores y Trabajadoras
   * Se agrega el campo de cantidad de cargos ocupados en el formulario de cargos para llevar un mjos control en cuanto a los cargos disponibles
   * Incorporación del histórico de disfrute de vacaciones en la carga masiva de solicitudes de vacaciones
   * Incorporación de carga masiva en la sección de ajustes de tablas salariales
   * Validaciones en la carga masiva de la hoja de tiempo
   * Eliminación de los datos de parámetros en el recibo de pago
   * Reporte de relación de trabajadores por nómina
   * Validaciones en años de servicio
   * Solicitudes de vacaciones fraccionadas
   * Validación del personal asociado a grupos supervisados
   * Ajuste en parámetros globales de nómina

 * Contabilidad
   * Reporte de estado del movimiento de patrimonio
   * Por cambios realizados en la ONAPRE, se modifican los nombres de los reportes:
     * Estado de resultados: Ahora se llama Estado de rendimiento financiero
     * Balance general: Ahora se llama Estado de situación financiera
   * Ajustes en tooltips de los formularios
   * Validación al registra conversiones de cuentas contables
   * Validación en la eliminación de conversiones de cuentas que estén asociadas a otros procesos
   * Agregada ventana modal con información detallada al realizar un reverso de asiento contable
   * Reporte de estado de fujo de efectivo en formato pdf y hoja de cálculo
   * Reporte de estado de rendimiento financiero en hoja de cálculo
   * Reporte de balance de comprobación
   * Adecuaciones al reporte de mayor analítico

## V1.0.4 (04-11-2023)

Ajustes y correcciones en:

 * Aplicación base
 * Módulos:
   * Talento Humano
   * Presupuesto
   * Finanzas
   * Contabilidad
   * Bienes
   * Almacén

Actualización de paquetes de composer

Actualización de paquetes de node

Actualización de documentación y variables de entorno en archivo README.md

Actualización de documentación de usuario

Se agregan funcionalidades en aplicación base:

 * Autenticación de usuarios a través de Directorio Activo (DA)
   * Verificación de usuario automática si el mismo es verificado por el DA
   * Condiciones para no enviar las notificaciones en el registro de usuario
   * Condiciones en el formulario de perfil de usuario para no permitir la modificación de contraseña
 * Bloqueo de pantalla por sesión inactiva
 * Agregado rol de Auditor
 * Condición para restringir acceso de sesiones simultáneas
 * Ajustes en configuración de cookies de sesión httponly y secure
 * Agregado middleware para establecer cabeceras de seguridad
   * Referrer-Policy
   * X-XSS-Protection
   * Content-Security-Policy
   * Access-Control-Allow-Origin
   * Access-Control-Allow-Methods
   * Access-Control-Allow-Headers
   * X-Frame-Options
   * X-Content-Type-Options
   * X-Permitted-Cross-Domain-Policies
   * Strict-Transport-Security

Se agregan funcionalidades en los módulos de:

 * Talento Humano:
   * Configuración:
     * Registros comunes:
       * Formulario de cargos: se agregó switch para responsabilidad y campo cantidad de cargos asignados
       * Formulario de parentescos: se incorporó formulario para agregar diferentes parentescos
       * Tipos de becas: se agregó formulario para tipo de becas
       * Formulario de coordinaciones: se agregó formulario para el registro de las coordinaciones que posea la organización usuaria
       * Formulario para niveles de responsabilidad: se agregó formulario de nivel de responsabilidad
     * Parámetros generales de nómina:
       * Formulario de tipos de excepción: se agregó formulario tipos de excepción
       * Formulario de parámetros globales: ajustes en formulario de parámetros globales
       * Formulario de conceptos: ajustes en formulario de conceptos
       * Formulario de tipos de pago: validación de campos
       * Formulario de políticas vacacionales: se agregó el campo "Intervalo en años de servicios para el aumento de días de disfrute"
       * Formulario de grupos supervisados: se agregó el formulario de grupos de supervisados
       * Formulario de parámetros en hoja de tiempo: se agregó el formulario de parámetros de hoja de tiempo
     * Expediente:
       * Carga masiva de datos laborales, personales, socioeconomicos, financieros y profesionales: ajustes en validaciones y formato
       * Formulario de datos contables: se agregó formulario para el registro de cuentas contables asociadas a los trabajadores
       * Datos personales: validación de campos y ajustes generales
       * Datos profesionales: se agregó sección de estudios en proceso
       * Datos socioeconómicos: ajustes en posición de campos dentro del formulario
       * Datos laborales: se agregaron los campos coordinación y ficha
     * Esquemas de guardia:
       * Formulario de esquemas de guardia: se agregó funcionalidad para la gestión de esquemas de guardia
     * Hoja de Tiempo:
       * Período Activo:
         * Hoja de tiempo: se agregó formulario de hoja de tiempo
       * Pendientes:
         * Hoja de tiempo pendientes: se agregó funcionalidad para la gestión de hojas de tiempo con estatus pendiente
     * Registros de nómina:
       * Formulario de registros de nómina: se realizaron validaciones generales
     * Registro ARI:
       * Formulario de registro ARI: se agregó formulario para el registro de datos de la planilla ARI
     * Archivo txt de Nómina:
       * Generar archivo de nómina: ajustes generales
     * Reportes:
       * Conceptos: validaciones generales
       * Relación de conceptos: se agregó el reporte de relación de conceptos

 * Presupuesto:
   * Se agrega valor por defecto a todos los campos fecha con la fecha actual del registro
   * Disponibilidad presupuestaria:
     * Pre compromiso de nómina: se agregó la gestión de pre compromiso de nómina
     * Agregado estatus anulado en la información de disponibilidad presupuestaria
   * Ejecución:
     * Compromiso:
       * Anulación de compromisos generados por nómina
       * Agregado estatus anulado

 * Finanzas:
   * Pago de nómina:
     * Orden de pago: se agregó la gestión de órdenes de pago desde nómina
     * Emisión de pago: se agregó la gestión de ejecuciones de pago desde nómina
   * Anulación del pago de nómina:
     * Emisión de pago: se agregó proceso para la anulación "Sin Remisión" y "Con remisión" de nómina
     * Orden de pago: se agregó proceso para la anulación "Con remisión" de nómina
     * Agregado estatus anulado en órdenes de pago, emisiones de pago y movimientos bancarios
     * Movimiento bancario automático: se agregó la anulación de movimiento bancario automático a partir de una nómina

 * Contabilidad:
   * Catálogo de Cuentas Patrimoniales:
     * Carga masiva: optimización y validaciones
   * Gestión de asientos contables:
     * Reverso automático
     * Validaciones generales
     * Optimización en la carga de asientos contables
   * Gestión de reportes
     * Ajustes y optimización en la carga de información para generar los reportes

 * Bienes
   * Gestión de depósitos:
     * Configuración: se agregó gestión de depósitos
     * Reportes: optimización en la carga de datos de reportes de bienes

 * Almacén:
   * Gestión de insumos: Optimización en la carga masiva de insumos

## V1.0.3 (12-07-2023)

Ajustes y correcciones en:

 * Aplicación base
 * Módulos:
   * Presupuesto
   * Talento Humano
   * Finanzas
   * Contabilidad
   * Bienes
   * Almacén
   * Compras
   * OAC

Actualización de paquetes de composer

Actualización de paquetes de node

Actualización de documentación en archivo README.md

Actualización de documentación de usuario

Ajustes y correcciones de funcionalidades en los módulos de:
 * Contabilidad:
   * Catálogo de cuentas
 * Finanzas:
   * Orden de pago
   * Ejecución de pago
 * Compras:
   * Requerimientos
   * Presupuesto base
   * Orden de compra / servicio
 * Presupuesto:
   * Disponibilidad presupuestaria
 * Bienes:
   * Ajustes en clasificador de bienes
 * Talento Humano:
   * Expediente
   * Configuración del módulo
   * Conceptos
   * Nómina
 * Cierre de ejercicio

Incorporación del módulo de Seguimiento de Proyectos con las funcionalidades de:
 * Configuración:
   * Formatos de código
   * Roles
   * Tipos de Proyectos
   * Tipos de Productos
   * Dependencias
   * Prioridad
   * Proyectos
   * Sub Proyectos
   * Productos
   * Actividades
   * Estatus de Actividades
   * Estatus de Entrega
 * Plan de actividades
   * Tareas
   * Avances
   * Reportes

**Actualizaciones a nivel de base de datos:**
**Módulos > Tablas > Campos**

 * Base > headquarters > nueva tabla para la gestión de sedes
 * Base > close_fiscal_years > nueva tabla para la gestión de años de cierre fiscal
 * Base > fiscal_years > agregado campo 'entries'
 * Base > receivers > agregado campos de relaciones morfológicas 'associateable_type' y 'associateable_id'
 * Base > sources > nueva tabla para el registro o gestión de fuentes de procesos
 * Contabilidad > accounting_accounts > se agregan campos 'resource' y 'egress'
 * Bienes > asset_required_items > eliminación de la tabla
 * Bienes > assets > se eliminan los campos 'color', 'address', 'inventory_serial', 'marca', 'model', 'parish_id'
 * Bienes > assets > se agregan los campos 'asset_details', 'acquisition_value', 'description', 'headquarter_id', 'department_id', 'code_sigecof', 'document_num'
 * Bienes > asset_depreciation_methods > nueva tabla para la gestión de métodos de depreciación
 * Bienes > asset_subcategories > se agregan campos de clave foránea 'accounting_account_debit' y 'accounting_account_asset'
 * Bienes > asset_books > nueva tabla para el registro del libro de bienes
 * Bienes > asset_depreciations > nueva tabla para la gestión de depreciación de bienes
 * Bienes > asset_depreciation_assets > nueva tabla para la gestión de depreciación de bienes
 * Bienes > asset_adjustment_assets > nueva tabla para los ajustes de bienes
 * Presupuesto > budget_sub_specific_formulations > se agrega campo 'date'
 * Presupuesto > budget_stages > se modifican los valores permitidos por el campo 'type'
 * O.A.C. > citizen_service_requests > se agregan los campos 'birth_date' y 'age'
 * O.A.C. > citizen_service_effect_types > nueva tabla para gestionar los tipos de servicio en la Oficina de Atención al Ciudadano
 * O.A.C. > citizen_service_indicators > nueva tabla para la gestión de indicadores
 * O.A.C. > citizen_service_add_indicators > nueva tabla para el registro de indicadores
 * O.A.C. > citizen_service_requests > se agrega campo 'other'
 * O.A.C. > citizen_service_registers > se modifica el campo 'project_name' por 'code'
 * Finanzas > finance_account_types > se agrega campo 'code'
 * Finanzas > finance_payment_deductions > se agrega campo 'mor'
 * Finanzas > finance_payment_executes > se agregan los campos 'payment_number' y 'description'
 * Finanzas > finance_payment_deductions > se agregan el campo de clave foránea 'deduction_id' y los campos de relaciones morfológicas 'deductionable_type' y 'deductionable_id'
 * Talento Humano > payroll_staffs > se modifica la relación al modelo de género
 * Talento Humano > payroll_gender > se elimina la tabla de género en el módulo de talento humano en virtud de ser parte de la aplicación base
 * Talento Humano > payroll_previous_jobs > se elimina el campo de clave foránea 'payroll_position_id' y se agrega el campo 'previous_position'
 * Talento Humano > payroll_concepts > se agrega el campo 'is_strict'
 * Talento Humano > payroll_payment_types > se agregan los campos 'individual', 'receipt' y campos de claves foráneas 'finance_bank_account_id', 'accounting_account_id', 'finance_payment_method_id' y 'accounting_entry_category_id'
 * Talento Humano > payroll_concepts > se agregan campos de clave foránea 'budget_project_id', 'budget_centralized_action_id' y 'budget_specific_action_id'
 * Talento Humano > payroll_text_files > nueva tabla para la gestión de archivos txt generados por el proceso de nómina
 * Talento Humano > payroll_payment_periods > agregado campo 'availability_status'
 * Talento Humano > payrolls > agregado campo 'code'
 * Compras > purchase_budgetary_availabilities > modificación en campo de 'purchase_quotation_id' a 'purchase_base_budgets_id'
 * Compras > purchase_suppliers > agregado campo 'person_type' y agregadas claves foráneas 'purchase_supplier_branch_id' y 'purchase_supplier_specialty_id'
 * Compras > purchase_quotations > agregado campo 'date'
 * Compras > purchase_branch_supplier > nueva tabla para gestionar la rama del proveedor
 * Compras > purchase_specialty_supplier > nueva tabla para gestionar especialidades de proveedores
 * Compras > purchase_types > agregado campo 'description'
 * Compras > purchase_pivot_models_to_requirement_items > agregado campo 'quantity'
 * Compras > purchase_direct_hires > se agrega campo 'status'
 * Compras > purchase_base_budgets > se agrega campo 'send_notify'
 * Compras > purchase_services > nueva tabla para la gestión de servicios de compra
 * Compras > purchase_requirements > se agrega campo 'requirement_type'
 * Compras > purchase_products > nueva tabla para la gestión de productos en el proceso de compra
 * Compras > purchase_services > se eliminan los campos 'date', 'institution_id' y 'history_tax_id'
 * Compras > purchase_requirement_items > se agregan los campos de clave foránea 'purchase_product_id', 'history_tax_id' y 'measurement_unit_id'
 * Comercialización > sale_orders > se agrega el campo 'code'
 * Comercialización > sale_quotes > se agrega los campos 'code', 'sale_charge_money_id' y 'sale_warehouse_method_id', y se elimina el campo 'sale_form_payment_id'
 * Comercialización > sale_setting_products > se agrega el campo de clave foránea 'sale_setting_product_type_id'
 * Comercialización > sale_list_subservices > se agrega el campo de clave foránea 'sale_type_good'
 * Comercialización > frecuencies > se agrega el campo 'days'
 * Almacén > warehouse_products > se agregan campos 'tax_id' y 'history_tax_id'
 * Almacén > warehouse_requests > se agrega campo 'request_date'
 * Almacén > warehouse_movements > se agrega campo 'reception_date'
 * Almacén > warehouse_inventory_products > se agregan campos 'unit_value', 'exist' y 'reserved'
 * Almacén > warehouse_inventory_product_movements > se agregan campos 'new_value' y 'quantity'
 * Almacén > warehouse_inventory_product_requests > se agregan campos 'quantity' y 'new_exist'

## V1.0.2 (16-12-2022)

Ajustes y correcciones en:
 * Aplicación base
 * Módulos:
    * Presupuesto
    * Talento Humano
    * Contabilidad
    * Finanzas
    * Bienes
    * Comercialización
    * Almacén
    * Compras
    * OAC

Actualización del archivo README

Elaboración y/o restructuración de los manuales de usuarios de los módulos:
  * Presupuesto
  * Compras
  * Contabilidad
  * Finanzas
  * Talento Humano
  * Bienes
  * Almacén
  * OAC
  * Aplicación base

Actualización de paquetes

Actualización de dependencias de paquetes


Incorporación de las siguientes funcionalidades:

  * Finanzas:
      * Ordenes de Pago
      * Emisiones de Pago.
      * Conciliación Bancaria
      * Movimientos bancarios
  * Presupuesto:
      * Reporte de disponibilidad Presupuestaria
      * Fuentes de financimiento
      * Tipos de financimiento
      * Reporte de mayor análitico
  * Talento Humano:
      * Importar/Exportar expediente de los trabajadores.
      * Generar nómina del personal
      * Datos laborales del trabajador
      * Dias feriados(Registros comunes)
      * Reporte de nómina
      * Conceptos
  * Compras
      * Disponibilidad Presupuestaria
      * Orden de compras
      * Cotizacion total o parcial
  * Bienes
      * Entrega de bienes
      * Asignación de bienesEntregas de bienes)
      * Imprimir acta de asignación de bienes


Se agregaron nuevos campos en las tablas de:
    * Almacén(Productos)
    * Bienes (Registrar bienes)
    * Bienes(Desincorporación)
    * Comercialización(Facturas)
    * Talento Humano(Ajuste en tablas salariales)
    * Talento humano(Tabuladores de nómina)
    * Compras(Requerimientos)
    * Compras(Orden de Compras)
    * Compras(Cotización)
    * Aplicación base(Crear usuarios)
    * Presupuesto(Formulación)
    * Finanzas (Cuentas bancarias)


**Actualizaciones a nivel de base de datos:**

**Módulos** > **Tablas** > **Campos**

* Base > 'fiscal_years' >	eliminado campo year
* Base > 'fiscal_years'	> cambiado Institution_id a bigInteger
* Base > 'fiscal_years' >	se establece como unico los índice 'year', 'institution_id', 'active'
* Base > required_documents	 > agregado campo Type
* Contabilidad > accounting_accounts > agregado campo original
* Bienes > 	assets	> agregado campo unico serial
* Bienes >  assets	> agregado campo 'purchase_supplier_id'
* Bienes >  asset_asignations' >	agregado campo 'location_place'
* Bienes >  asset_requests >	agregado campo 'address'
* Bienes >  asset_requests >	agregado campo 'country_id'
* Bienes > 	asset_requests >	agregado campo 'estate_id'
* Bienes >	asset_requests >	agregado campo 'municipality_id'
* Bienes >	asset_requests >	agregado campo 'parish_id'
* Bienes >	assets >	agregado campo'color'
* Bienes >	assets >	agregado campo 'asset_institutional_code'
* Bienes >	asset_asignation_deliveries	> agregado campo 'id'	Creada  tabla
* Bienes >	asset_asignation_deliveries	> agregado campo 'state'
* Bienes >	asset_asignation_deliveries	> agregado campo observation'
* Bienes >	asset_asignation_deliveries	> agregado campo  'asset_asignation_id'
* Bienes >	asset_asignation_deliveries	> agregado campo 'user_id'
* Bienes >	asset_asignations' > agregado > campo 'state'
* Bienes >	asset_asignations' >	agregado campo 'ids_assets'
* Bienes >	asset_asignations' >	agregado campo authorized_by_id
* Bienes >	asset_asignations' >	agregado campo'formed_by_id'
* Bienes >	asset_asignations' >	agregado campo 'delivered_by_id'
* Bienes >	asset_disincorporations' >	agregado campo 'authorized_by_id'
* Bienes >	asset_disincorporations' >	agregado campo 'formed_by_id'
* Bienes >	asset_disincorporations' >	agregado campo 'produced_by_id'
* Bienes >	asset_asignation_deliveries' >	agregado campo 'approved_by_id'
* Bienes >	asset_asignation_deliveries' >	agregado campo 'received_by_id'
* Bienes >	asset_asignation_deliveries' >	agregado campo 'ids_assets'
* Bienes >	asset_request_events' >	agregado campo 'ids_assets'
* Presupuesto > budget_compromise_details' >	cambio el campo 'budget_account_id' a nullable
* Presupuesto > budget_compromise_details' >	Se elimino el campo 'budget_compromise_details_formulation_fk'
* Presupuesto > budget_compromise_details' >	agregado campo 'budget_sub_specific_formulation_id'
* Presupuesto > budget_financement_sources' >	agregado campo 'id'	se crea la tabla 'budget_financement_sources'
* Presupuesto > budget_financement_sources' >	agregado campo 'name'
* Presupuesto > budget_financement_sources' >	agregado campo 'budget_financement_type_id'
* Presupuesto > budget_account_opens' >	agregado campo total_year_amount_m
* Presupuesto > budget_projects'  > 	agregado campo 'name'	se crea la tabla budget_projects
* Presupuesto > budget_financement_types' >	agregado campo 'id'	se crea la tabla 'budget_financement_types'
* Presupuesto > budget_financement_types' >	agregado campo 'name'
* Presupuesto > budget_sub_specific_formulations' >	agregado campo 'budget_financement_type_id'
* Presupuesto > budget_sub_specific_formulations' >	agregado campo'budget_financement_source_id'
* Presupuesto > budget_sub_specific_formulations' >	agregado campo 'financement_amount'
* Presupuesto > budget_projects' >	agregado campo 'from_date'
* Presupuesto > budget_projects' >	agregado campo'to_date'
* Presupuesto > budget_projects' >	agregado campo 'description'
* Presupuesto > budget_centralized_actions'  >	agregado campo 'from_date'
* Presupuesto > budget_centralized_actions'  >	agregado campo 'to_date'
* Presupuesto > budget_centralized_actions'  >	agregado campo 'ca_description'
* Presupuesto > budget_centralized_actions'  >	agregado campo 'name'
* Presupuesto > budget_projects' >	agregado campo 'name'

* Finanzas >'finance_setting_bank_reconciliation_files' >	agregado campo 'balance_according_bank'
* Finanzas > 'finance_setting_bank_reconciliation_files' > agregado campo'position_balance_according_bank'
* Finanzas > finance_banking_movements'	> agregado campo'id	Creada  tabla 'finance_banking_movements'
* Finanzas > finance_banking_movements' > agregado campo 'payment_date'
* Finanzas > finance_banking_movements' >	agregado campo 'transaction_type'
* Finanzas > finance_banking_movements' >	agregado campo 'reference'
* Finanzas > finance_banking_movements' >	agregado campo 'concept'
* Finanzas > finance_banking_movements' >	agregado campo  'amount'
* Finanzas > finance_banking_movements' >	agregado campo  'financebank_account_id'
* Finanzas > finance_banking_movements' >	agregado campo  'currency_id'
	si existe el modulo  'Contabilidad'
	finance_banking_movements' >	agregado campo 'accounting_entry_id
	si existe el modulo  'Presupuesto'
	finance_banking_movements' >	agregado campo 'budget_compromise_id'
* Talento Humano > 'payroll_benefits_policies' >	eliminado campo 'salary_type'
* Talento Humano > 'payroll_salary_adjustments' >	creado campo 'increase_of_date'	Creada tabla
* Talento Humano > 'payroll_salary_adjustments' >	creado campo 'increase_of_type'
* Talento Humano > 'payroll_salary_adjustments' >	creado campo 'value'
* Talento Humano > 'payroll_salary_adjustments' >	creado campo 'payroll_salary_tabulator_id'
* Talento Humano > 'payroll_holidays' >	creado campo date
* Talento Humano > 'payroll_holidays' >	creado campo description
* Talento Humano > 'payroll_vacation_policies' >	Campos cambiado 'business_days' a boolean con false por defecto
* Talento Humano > 'payroll_vacation_policies' >	Campos cambiados “old_jobs” a boolean con false por defecto
* Talento Humano > 'payroll_vacation_policies' >	Campos cambiados 'vacation_pay_days' a integer nullable
* Talento Humano > 'payroll_vacation_policies' >	Eliminado campo 'payment_calculation'
* Talento Humano > 'payroll_vacation_policies' >	Eliminado campo 'vacation_pay_days'
* Talento Humano > 'payroll_vacation_policies' >	Eliminado campo 'max_days_advance'
* Talento Humano > 'payroll_permission_policies' >	creado campo 'business_days' tipo boolean con false por defecto
* Talento Humano > 'payroll_vacation_policies' >	Creado campo 'from_year' tipò integer nullable
* Talento Humano > 'payroll_staff_payrolls' >	eliminado campo 'assignments'
* Talento Humano > 'payroll_staff_payrolls' >	eliminado campo 'deductions'
* Talento Humano > 'payroll_staff_payrolls' >	creado campo 'concept_type'
* Talento Humano > 'payroll_vacation_requests' >	cambiado campo 'vacation_period_year' a tipo longText
* Talento Humano > 'payrolls' >	cambiado campo 'payroll_parameters' a Json nullable
* Talento Humano > 'payroll_financials' >	creado campo 'id'	Tabla creada 'payroll_financials'
* Talento Humano > 'payroll_financials' >	creado campo 'payroll_staff_id'
* Talento Humano > 'payroll_financials' > creado campo 'restrict'
* Talento Humano > 'payroll_financials'	> creado campo 'finance_bank_id'
* Talento Humano > 'payroll_financials' >	creado campo 'finance_account_type_id'
* Talento Humano > 'payroll_financials'	> creado campo 'payroll_account_number'
* Talento Humano > 'payrolls'	 > cambiado campo 'payroll_parameters' a Json nullable
* Talento Humano > 'payroll_employments'  >	cambiado campo 'years_apn' a string (50) nullable
* Talento Humano > 'payroll_payment_types' >	cambiado campo 'start_date' a tipo fecha
* Talento Humano > 'payroll_payment_types' >	Cambiado campo 'payment_relationship' a enum
* Talento Humano > 'payroll_acknowledgment_files' >	Cambiado campo 'name' a string (200)
* Talento Humano > 'payroll_concept_types' >	cambiado campo 'sign' a string (2)
* Compras >	'purchase_direct_hires'  >	agregado campo  'institution_id'
* Compras >	'purchase_direct_hires' >	agregado campo  'purchase_supplier_object_id'
* Compras >	'purchase_base_budgets' > agregado campo 'tax_id'
* Compras >	'purchase_direct_hires' > agregado campo  'payment_methods'
* Compras >	'purchase_base_budgets' > agregado campo  'orderable'
* Compras >	'purchase_direct_hires' > agregado campo  'prepared_by_id'
* Compras >	'purchase_direct_hires' > agregado campo  'reviewed_by_id'
* Compras >	'purchase_direct_hires' > agregado campo  'verified_by_id'
* Compras >	'purchase_direct_hires' > agregado campo  'first_signature_id'
* Compras >	'purchase_direct_hires' > agregado campo  'second_signature_id'
* Compras >	'purchase_direct_hires'	> agregado campo  'code'
* Compras >	'purchase_direct_hires' >	agregado campo  'date'
* Compras >	'purchase_suppliers' >	agregado campo 'accounting_account_id'
* Compras >	'purchase_direct_hires' >	agregado campo  'receiver'
* Compras >	'purchase_base_budgets' >	agregado campo  'prepared_by_id'
* Compras >	'purchase_base_budgets' >	agregado campo  'reviewed_by_id'
* Compras >	'purchase_base_budgets' >	agregado campo  'verified_by_id'
* Compras >	'purchase_base_budgets' >	agregado campo   'first_signature_id'
* Compras >	'purchase_base_budgets' >	agregado campo   'second_signature_id'
* Compras >	'purchase_requirements' >	agregado campo  'prepared_by_id'
* Compras >	'purchase_requirements' >	agregado campo  'reviewed_by_id'
* Compras >	'purchase_requirements' >	agregado campo  'verified_by_id'
* Compras >	'purchase_requirements' >	agregado campo   'first_signature_id'
* Compras >	'purchase_requirements' >	agregado campo   'second_signature_id'
* Compras >	'purchase_quotations' >	agregado campo  'orderable'
* Compras >	'purchase_base_budgets' >	cambiado  campo 'status'
* Compras >	'purchase_types' >	agregado campo   'documents_id'
* Compras >	'purchase_direct_hires' > agregado campo  'purchase_type_id'
* Compras >	'purchase_direct_hires' >	agregado campo  'due_date'
* Compras >	'purchase_direct_hires'  >	agregado campo   'hiring_number'
* Compras >	'purchase_budgetary_availabilities'  >		Creada  tabla 'purchase_budgetary_availabilities'
* Compras >	'purchase_budgetary_availabilities' >	agregado campo   'id'
* Compras >	'purchase_budgetary_availabilities' > agregado campo  'item_code'
* Compras >	'purchase_budgetary_availabilities' >	agregado campo   'item_name'
* Compras >	'purchase_budgetary_availabilities' >	agregado campo   'amount'
* Compras >	'purchase_budgetary_availabilities' >	agregado campo  'description'
* Compras >	'purchase_budgetary_availabilities' >	agregado campo   'availability'
* Compras >	'purchase_direct_hires' >	cambiado campo 'reviewed_by_id' a nullable
* Compras >	'purchase_direct_hires' >	cambiado  campo 'first_signature_id' a nullable
* Compras >	'purchase_direct_hires' >	cambiado  campo 'second_signature_id' a nullable
* Compras >	'purchase_direct_hires' >	cambiado  campo 'verified_by_id' a nullable
* Compras >	'purchase_plans' >	borrado campo 'purchase_processes_id'
* Compras >	'purchase_plans' >	borrado campo 'purchase_processes_id'
* Comercialización > 'sale_bill_inventory_products' >	agregado campo  softDeletes
* Comercialización > 'sale_good_to_be_traded_payroll_staff'	> agregado campo   id	Creada  tabla 'sale_good_to_be_traded_payroll_staff'
* Comercialización > 'sale_good_to_be_traded_payroll_staff' >	agregado campo   'sale_goods_to_be_traded_id'
* Comercialización > 'sale_good_to_be_traded_payroll_staff' >	agregado campo   'payroll_staff_id'
		* Almacén >	'warehouse_products'	> agregado campo  'budget_account_id'
	'warehouse_inventory_product_requests'	> agregado campo  'new_exist'


## V1.0.1 (14-05-2022)

Ajustes y correcciones en:

* Aplicación base.
* Módulos:
  * Presupuesto
  * Oficina de Atención al Ciudadano (OAC)
  * Talento Humano
  * Compras
  * Comercialización

## V1.0.0 (09-04-2022)

Primera versión del ERP Kavac la cual incluye:

* Aplicación base que incorpora funcionalidades de uso general
* Auditoria de Registros
* Bitácora de Eventos
* Gestión de usuarios, roles y permisos
* Restauración de registros eliminados
* Gestión de registros comunes
* Gestión de organismos
* Perfil de usuario
* Bloqueo de pantalla
* Modulos:
  * Contabilidad
  * Bienes
  * Presupuesto
  * Oficina de Atención al Ciudadano (OAC)
  * Firma Electrónica
  * Finanzas
  * Talento Humano
  * Compras
  * Comercialización
  * Almacén
