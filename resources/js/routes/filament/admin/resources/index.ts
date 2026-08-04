import adminRoles from './admin-roles'
import adminUsers from './admin-users'
import clientVersions from './client-versions'
import systemSettings from './system-settings'
import users from './users'

const resources = {
    adminRoles: Object.assign(adminRoles, adminRoles),
    adminUsers: Object.assign(adminUsers, adminUsers),
    clientVersions: Object.assign(clientVersions, clientVersions),
    systemSettings: Object.assign(systemSettings, systemSettings),
    users: Object.assign(users, users),
}

export default resources