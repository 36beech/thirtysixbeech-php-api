export const Button = ({ children, ...props }) => {
  return <button {...props} className="bg-sky-700 text-white p-4 my-4">{children}</button>
}