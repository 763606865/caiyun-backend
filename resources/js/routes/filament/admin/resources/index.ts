import adminRoles from './admin-roles'
import adminUsers from './admin-users'
import categories from './categories'
import clientVersions from './client-versions'
import contents from './contents'
import customerRequirements from './customer-requirements'
import licensePackages from './license-packages'
import media from './media'
import operationLogs from './operation-logs'
import systemSettings from './system-settings'
import tags from './tags'
import users from './users'

const resources = {
    adminRoles: Object.assign(adminRoles, adminRoles),
    adminUsers: Object.assign(adminUsers, adminUsers),
    categories: Object.assign(categories, categories),
    clientVersions: Object.assign(clientVersions, clientVersions),
    contents: Object.assign(contents, contents),
    customerRequirements: Object.assign(customerRequirements, customerRequirements),
    licensePackages: Object.assign(licensePackages, licensePackages),
    media: Object.assign(media, media),
    operationLogs: Object.assign(operationLogs, operationLogs),
    systemSettings: Object.assign(systemSettings, systemSettings),
    tags: Object.assign(tags, tags),
    users: Object.assign(users, users),
}

export default resources