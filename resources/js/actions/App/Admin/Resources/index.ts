import AdminRoles from './AdminRoles'
import AdminUsers from './AdminUsers'
import ClientVersions from './ClientVersions'
import LicensePackages from './LicensePackages'
import SystemSettings from './SystemSettings'
import Users from './Users'

const Resources = {
    AdminRoles: Object.assign(AdminRoles, AdminRoles),
    AdminUsers: Object.assign(AdminUsers, AdminUsers),
    ClientVersions: Object.assign(ClientVersions, ClientVersions),
    LicensePackages: Object.assign(LicensePackages, LicensePackages),
    SystemSettings: Object.assign(SystemSettings, SystemSettings),
    Users: Object.assign(Users, Users),
}

export default Resources