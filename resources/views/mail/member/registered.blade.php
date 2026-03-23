<x-mail::message>
# Estimado(a) {{ $user->name }}

# Bienvenidos a {{ config('app.name') }}

Usted se registró exitosamente.

  El perfil de su usuario es de tipo "Visitante". Bajo este perfil,
  usted podrá navegar en el sitio y mantener sus favoritos.

  Para publicar sus emprendimientos en el portal, usted debe convertirse
  a un Afiliado. Para afiliarse, acceda su perfil y haga clic en
  "Solicitar Afiliación" y siga los pasos.

  Una vez aprobado su solicitud, usted podrá publicar sus emprendimientos.

  # Dear {{ $user->name }}

  # Welcome to {{ config('app.name') }}

  You have registered successfully.

  Your user profile is of type "Visitor". Under this profile, you will be able
  to browse the site and keep your favorites.

  To publish your entrepreneurship on the portal, you must become an Affiliate.
  To become a member, access your profile click on "Solicitar Afiliación" and follow the steps.
  Once your application is approved, you will be able to publish your entrepreneurship.

  Gracias/Thankyou

  {{ config('app.name') }}
</x-mail::message>
