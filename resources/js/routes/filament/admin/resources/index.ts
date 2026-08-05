import adminRoles from './admin-roles'
import adminUsers from './admin-users'
import clientVersions from './client-versions'
import licensePackages from './license-packages'
import systemSettings from './system-settings'
import users from './users'

const resources = {
    adminRoles: Object.assign(adminRoles, adminRoles),
    adminUsers: Object.assign(adminUsers, adminUsers),
    clientVersions: Object.assign(clientVersions, clientVersions),
    licensePackages: Object.assign(licensePackages, licensePackages),
    systemSettings: Object.assign(systemSettings, systemSettings),
    users: Object.assign(users, users),
}

export default resources