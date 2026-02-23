// Login page bypasses the parent admin layout's auth requirement
export default function LoginLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return <>{children}</>;
}
