-- Países
INSERT INTO `ubicacion_pais` (`pais_id`, `nombre`) VALUES (1, 'Venezuela');

-- Estados de Venezuela (conjunto completo para permitir direcciones en cualquier estado)
INSERT INTO `ubicacion_estado` (`estado_id`, `pais_id`, `nombre`) VALUES
  (1,  1, 'Amazonas'),
  (2,  1, 'Anzoátegui'),
  (3,  1, 'Apure'),
  (4,  1, 'Aragua'),
  (5,  1, 'Barinas'),
  (6,  1, 'Bolívar'),
  (7,  1, 'Carabobo'),
  (8,  1, 'Cojedes'),
  (9,  1, 'Delta Amacuro'),
  (10, 1, 'Distrito Capital'),
  (11, 1, 'Falcón'),
  (12, 1, 'Guárico'),
  (13, 1, 'La Guaira'),
  (14, 1, 'Lara'),
  (15, 1, 'Mérida'),
  (16, 1, 'Miranda'),
  (17, 1, 'Monagas'),
  (18, 1, 'Nueva Esparta'),
  (19, 1, 'Portuguesa'),
  (20, 1, 'Sucre'),
  (21, 1, 'Táchira'),
  (22, 1, 'Trujillo'),
  (23, 1, 'Yaracuy'),
  (24, 1, 'Zulia');

-- Municipios del estado Portuguesa (foco operativo del club)
INSERT INTO `ubicacion_municipio` (`municipio_id`, `estado_id`, `nombre`) VALUES
  (1, 19, 'Páez'),
  (2, 19, 'Araure'),
  (3, 19, 'Esteller'),
  (4, 19, 'Guanare'),
  (5, 19, 'Guanarito'),
  (6, 19, 'Monseñor José Vicente de Unda'),
  (7, 19, 'Ospino'),
  (8, 19, 'Papelón'),
  (9, 19, 'San Genaro de Boconoíto'),
  (10, 19, 'San Rafael de Onoto'),
  (11, 19, 'Santa Rosalía'),
  (12, 19, 'Sucre'),
  (13, 19, 'Turén');

-- Parroquias del municipio Páez (sede del club)
INSERT INTO `ubicacion_parroquia` (`parroquia_id`, `municipio_id`, `nombre`) VALUES
  (1, 1, 'Payara'),
  (2, 1, 'Pimpinela'),
  (3, 1, 'Ramón Peraza'),
  (4, 1, 'Acarigua');
