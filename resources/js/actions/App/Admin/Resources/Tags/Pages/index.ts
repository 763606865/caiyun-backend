import ListTags from './ListTags'
import CreateTag from './CreateTag'
import EditTag from './EditTag'

const Pages = {
    ListTags: Object.assign(ListTags, ListTags),
    CreateTag: Object.assign(CreateTag, CreateTag),
    EditTag: Object.assign(EditTag, EditTag),
}

export default Pages