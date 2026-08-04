import AdminRoles from './AdminRoles'
import AdminUsers from './AdminUsers'
import ClientVersions from './ClientVersions'
import SystemSettings from './SystemSettings'
import Users from './Users'

const Resources = {
    AdminRoles: Object.assign(AdminRoles, AdminRoles),
    AdminUsers: Object.assign(AdminUsers, AdminUsers),
    ClientVersions: Object.assign(ClientVersions, ClientVersions),
    SystemSettings: Object.assign(SystemSettings, SystemSettings),
    Users: Object.assign(Users, Users),
}

export default Resources