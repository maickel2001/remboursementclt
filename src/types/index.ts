export interface User {
  id: string;
  email: string;
  firstName: string;
  lastName: string;
  phone: string;
  address: string;
  role: 'client' | 'admin';
  profilePicture?: string;
  createdAt: string;
}

export interface Remboursement {
  id: string;
  userId: string;
  montantTotal: number;
  remboursementEffectue: number;
  resteARembourser: number;
  moyenPaiement: string;
  typeCarte?: string;
  numeroCarte?: string;
  codeRechargement?: string;
  status: 'en_attente' | 'valide' | 'refuse';
  createdAt: string;
  updatedAt: string;
}

export interface AuthContextType {
  user: User | null;
  login: (email: string, password: string) => Promise<boolean>;
  register: (userData: Omit<User, 'id' | 'createdAt'> & { password: string }) => Promise<boolean>;
  logout: () => void;
  updateProfile: (userData: Partial<User>) => void;
}

export interface AppSettings {
  siteName: string;
  contactEmail: string;
  maintenanceMode: boolean;
}