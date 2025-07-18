export function getClsByXtype(xtype: string): string {
    if (!xtype) return ''

    const parts: string[] = xtype.split('.')
    const first: string = parts[0]
    const classes: string[] = [first]
    parts.slice(1).forEach((part) => {
        classes.push(`${first}-${part}`)
        classes.push(part)
    })
    if (parts.length > 2) {
        classes.push(parts.join('-'))
    }
    return classes.join(' ')
}
