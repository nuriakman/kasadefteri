export interface LoginCredentials {
  email: string
  password: string
}

export interface LoginResponse {
  token: string
  user: UserInfo
}

export interface UserInfo {
  id: number
  email: string
  name: string
  created_at: string
  updated_at: string
}

export interface GoogleLoginResponse {
  access_token: string
}
