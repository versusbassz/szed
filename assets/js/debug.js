
export function log (data, name = '') {
    if (szed.debug === true) {
        
        if (name) {
            console.log(name);
        }
        
        console.log(data);
    }
}
