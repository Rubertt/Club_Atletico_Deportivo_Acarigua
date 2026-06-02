-- Roles del sistema
INSERT INTO `roles_usuarios` (`rol_id`, `nombre_rol`, `descripcion`) VALUES
  (1, 'super_usuario', 'Soporte técnico y desarrollo con acceso total.'),
  (2, 'administrador', 'Administrador ordinario con gestión de atletas, categorías, asistencias y usuarios.'),
  (3, 'entrenador',    'Entrenador con gestión técnico-deportiva de los atletas.'),
  (4, 'directivo',     'Directivo con acceso administrativo completo y funciones técnico-deportivas.');
