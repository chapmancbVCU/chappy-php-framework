/**
 * Sample view to demonstrate unit testing.
 * @property {object} user A user for testing.
 * @param {InputProps} param0 
 * @returns {JSX.Element} The contents of SampleView.
 */
export default function SampleView({ user }) {
    const name = user.fname ?? 'Guest';
    return (
        <div className="container">
            <h1 className="display-4">Hello, {name} 👋</h1>
        </div>
    );
}