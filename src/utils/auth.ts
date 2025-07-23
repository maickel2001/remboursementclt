import { User } from '../types';

export const initializeAdminUser = (): void => {
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
};