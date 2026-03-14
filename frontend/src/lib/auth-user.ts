export type UserRole = "user" | "helpdesk_agent" | "admin";

export interface AuthUser {
  id: number | string | null;
  name: string;
  email: string | null;
  role: UserRole;
}

export function normalizeRole(rawRole: unknown): UserRole {
  if (rawRole === "helpdesk_agent") return rawRole;
  if (rawRole === "admin") return rawRole;
  return "user";
}

export function normalizeUser(rawUser: unknown): AuthUser {
  if (!rawUser || typeof rawUser !== "object") {
    return {
      id: null,
      name: "User",
      email: null,
      role: "user",
    };
  }

  const user = rawUser as Record<string, unknown>;

  return {
    id: typeof user.id === "number" || typeof user.id === "string" ? user.id : null,
    name: typeof user.name === "string" && user.name.trim() !== "" ? user.name : "User",
    email: typeof user.email === "string" && user.email.trim() !== "" ? user.email : null,
    role: normalizeRole(user.role),
  };
}
