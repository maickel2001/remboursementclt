import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { User, AuthContextType } from '../types';
import toast from 'react-hot-toast';

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};

interface AuthProviderProps {
  children: ReactNode;
}

export const AuthProvider: React.FC<AuthProviderProps> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);

  useEffect(() => {
    const savedUser = localStorage.getItem('currentUser');
    if (savedUser) {
      setUser(JSON.parse(savedUser));
    }

    // Initialize with admin user if no users exist
    const users = JSON.parse(localStorage.getItem('users') || '[]');
    if (users.length === 0) {
      const adminUser: User = {
        id: 'admin-1',
        email: 'admin@remboursepro.com',
        firstName: 'Admin',
        lastName: 'RemboursePRO',
        phone: '+33 1 23 45 67 89',
        address: '123 Avenue des Remboursements, 75001 Paris',
        role: 'admin',
        createdAt: new Date().toISOString()
      };
      localStorage.setItem('users', JSON.stringify([adminUser]));
      localStorage.setItem('passwords', JSON.stringify({ 'admin@remboursepro.com': 'admin123' }));
    }
  }, []);

  const login = async (email: string, password: string): Promise<boolean> => {
    const users: User[] = JSON.parse(localStorage.getItem('users') || '[]');
    const passwords = JSON.parse(localStorage.getItem('passwords') || '{}');
    
    const foundUser = users.find(u => u.email === email);
    if (foundUser && passwords[email] === password) {
      setUser(foundUser);
      localStorage.setItem('currentUser', JSON.stringify(foundUser));
      toast.success(`Bienvenue ${foundUser.firstName} !`);
      return true;
    }
    
    toast.error('Email ou mot de passe incorrect');
    return false;
  };

  const register = async (userData: Omit<User, 'id' | 'createdAt'> & { password: string }): Promise<boolean> => {
    const users: User[] = JSON.parse(localStorage.getItem('users') || '[]');
    const passwords = JSON.parse(localStorage.getItem('passwords') || '{}');
    
    if (users.find(u => u.email === userData.email)) {
      toast.error('Un compte avec cet email existe déjà');
      return false;
    }

    const newUser: User = {
      id: `user-${Date.now()}`,
      email: userData.email,
      firstName: userData.firstName,
      lastName: userData.lastName,
      phone: userData.phone,
      address: userData.address,
      role: userData.role,
      createdAt: new Date().toISOString()
    };

    users.push(newUser);
    passwords[userData.email] = userData.password;
    
    localStorage.setItem('users', JSON.stringify(users));
    localStorage.setItem('passwords', JSON.stringify(passwords));
    
    toast.success('Compte créé avec succès !');
    return true;
  };

  const logout = () => {
    setUser(null);
    localStorage.removeItem('currentUser');
    toast.success('Déconnexion réussie');
  };

  const updateProfile = (userData: Partial<User>) => {
    if (!user) return;
    
    const updatedUser = { ...user, ...userData };
    setUser(updatedUser);
    localStorage.setItem('currentUser', JSON.stringify(updatedUser));
    
    const users: User[] = JSON.parse(localStorage.getItem('users') || '[]');
    const updatedUsers = users.map(u => u.id === user.id ? updatedUser : u);
    localStorage.setItem('users', JSON.stringify(updatedUsers));
    
    toast.success('Profil mis à jour avec succès !');
  };

  return (
    <AuthContext.Provider value={{ user, login, register, logout, updateProfile }}>
      {children}
    </AuthContext.Provider>
  );
};