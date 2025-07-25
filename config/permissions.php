<?php

return [
  ['id' => 'adm', 'parent' => '#', 'text' => 'Administración'],
  ['id' => 'app', 'parent' => '#', 'text' => 'Aplicación'],

  ['id' => 'Admin.Role', 'parent' => 'adm', 'text' => 'Roles'],
  ['id' => 'Admin.Role.viewAny', 'parent' => 'Admin.Role', 'text' => 'Listar'],
  ['id' => 'Admin.Role.create', 'parent' => 'Admin.Role', 'text' => 'Agregar'],
  ['id' => 'Admin.Role.view', 'parent' => 'Admin.Role', 'text' => 'Ver'],
  ['id' => 'Admin.Role.update', 'parent' => 'Admin.Role', 'text' => 'Editar'],
  ['id' => 'Admin.Role.delete', 'parent' => 'Admin.Role', 'text' => 'Eliminar'],
  ['id' => 'Admin.Role.toggleflag-active', 'parent' => 'Admin.Role', 'text' => 'Alternar Activo'],
  ['id' => 'Admin.Role.toggleflag-blocked', 'parent' => 'Admin.Role', 'text' => 'Alternar Admin'],

  ['id' => 'Admin.User', 'parent' => 'adm', 'text' => 'Usuarios'],
  ['id' => 'Admin.User.viewAny', 'parent' => 'Admin.User', 'text' => 'Listar'],
  ['id' => 'Admin.User.create', 'parent' => 'Admin.User', 'text' => 'Agregar'],
  ['id' => 'Admin.User.view', 'parent' => 'Admin.User', 'text' => 'Ver'],
  ['id' => 'Admin.User.update', 'parent' => 'Admin.User', 'text' => 'Editar'],
  ['id' => 'Admin.User.delete', 'parent' => 'Admin.User', 'text' => 'Eliminar'],
  ['id' => 'Admin.User.toggleflag-active', 'parent' => 'Admin.User', 'text' => 'Alternar Activo'],
  ['id' => 'Admin.User.toggleflag-blocked', 'parent' => 'Admin.User', 'text' => 'Alternar Bloqueado'],
  ['id' => 'Admin.User.toggleflag-can_approve', 'parent' => 'Admin.User', 'text' => 'Alternar Aprobar'],
  ['id' => 'Admin.User.set-password', 'parent' => 'Admin.User', 'text' => 'Fijar Contraseña'],

  ['id' => 'Admin.Config', 'parent' => 'adm', 'text' => 'Configuración'],
  ['id' => 'Admin.Config.viewAny', 'parent' => 'Admin.Config', 'text' => 'Listar'],
  ['id' => 'Admin.Config.view', 'parent' => 'Admin.Config', 'text' => 'Ver'],
  ['id' => 'Admin.Config.update', 'parent' => 'Admin.Config', 'text' => 'Editar'],

  ['id' => 'Admin.Member', 'parent' => 'app', 'text' => 'Admin Afiliados'],
  ['id' => 'Admin.Member.viewAny', 'parent' => 'Admin.Member', 'text' => 'Listar'],
  ['id' => 'Admin.Member.view', 'parent' => 'Admin.Member', 'text' => 'Ver'],
  ['id' => 'Admin.Member.delete', 'parent' => 'Admin.Member', 'text' => 'Eliminar'],
  ['id' => 'Admin.Member.approve-membership', 'parent' => 'Admin.Member', 'text' => 'Aprobar Afiliación'],

  ['id' => 'Admin.Venture', 'parent' => 'app', 'text' => 'Admin Emprendimientos'],
  ['id' => 'Admin.Venture.viewAny', 'parent' => 'Admin.Venture', 'text' => 'Listar'],
  ['id' => 'Admin.Venture.create', 'parent' => 'Admin.Venture', 'text' => 'Agregar'],
  ['id' => 'Admin.Venture.view', 'parent' => 'Admin.Venture', 'text' => 'Ver'],
  ['id' => 'Admin.Venture.delete', 'parent' => 'Admin.Venture', 'text' => 'Eliminar'],
  ['id' => 'Admin.Venture.approve-venture', 'parent' => 'Admin.Venture', 'text' => 'Aprobar Emprendimiento'],
  ['id' => 'Admin.Venture.force-reject-venture', 'parent' => 'Admin.Venture', 'text' => 'Desaprobar Emprendimiento'],

  ['id' => 'Member', 'parent' => 'app', 'text' => 'Afiliados'],
  ['id' => 'Member.requestAffiliation', 'parent' => 'Member', 'text' => 'Solicitar Afiliación'],
  ['id' => 'Member.createVenture', 'parent' => 'Member', 'text' => 'Crear Emprendimiento'],
  ['id' => 'Member.editVenture', 'parent' => 'Member', 'text' => 'Editar Emprendimiento'],
  ['id' => 'Member.dupVenture', 'parent' => 'Member', 'text' => 'Duplicar Emprendimiento'],
  ['id' => 'Member.requestVentureApproval', 'parent' => 'Member', 'text' => 'Solicitar Aprobación de Emprendimiento'],
  ['id' => 'Member.extendVentureValidity', 'parent' => 'Member', 'text' => 'Extender Emprendimiento'],
  // ['id' => 'Member.Media.viewAny', 'parent' => 'Member', 'text' => 'Medios Listar'],
  // ['id' => 'Member.Media.create', 'parent' => 'Member', 'text' => 'Medio Crear'],
  // ['id' => 'Member.Media.edit', 'parent' => 'Member', 'text' => 'Medio Editar'],
  // ['id' => 'Member.Media.view', 'parent' => 'Member', 'text' => 'Medio Ver'],
  // ['id' => 'Member.Media.delete', 'parent' => 'Member', 'text' => 'Medio Eliminar'],

];
