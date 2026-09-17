-- Migración del módulo view/estadisticas/
-- Fecha de cierre de tareas (métricas de rendimiento reales).
-- Ejecutar una vez en la BD colsoftco.
ALTER TABLE `tareas`
  ADD COLUMN IF NOT EXISTS `fecha_cierre` DATETIME NULL DEFAULT NULL AFTER `fecha_creacion`;
