import { useState, useEffect } from "react";
import { useForm } from "@inertiajs/react";
import { Link } from "@inertiajs/inertia-react";
import { Inertia } from "@inertiajs/inertia";
import { ToastContainer, toast } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import 'react-phone-number-input/style.css'
import PhoneInputWithCountrySelect from "react-phone-number-input";
interface PageProps {
    auth: {
        user: {
            id: number;
            username: string;
            email: string;
            gender: string;
            description: string;
        } | null;
    };
    countries: { name: string; iso_code_2: string }[];
    flash?: {
        success?: string;
        error?: string;
    };
}

const CreateCompany: React.FC<PageProps> = ({ countries, auth, flash }) => {
    const { data, setData, processing, errors, post } = useForm({
        name: "",
        email: "",
        phone_number: "",
        street_address: "",
        city: "",
        state: "",
        postal_code: "",
        country: "",
    });

    const [states, setStates] = useState<{ name: string; state_code: string }[]>([]);
    const [cities, setCities] = useState<{ name: string }[]>([]);
    const [loadingStates, setLoadingStates] = useState(false);
    const [loadingCities, setLoadingCities] = useState(false);
    const [stateError, setStateError] = useState("");
    const [cityError, setCityError] = useState("");

    const handleCountryChange = async (e: React.ChangeEvent<HTMLSelectElement>) => {
        const country = e.target.value;
        setData("country", country);
        setData("state", "");
        setData("city", "");
        setStates([]);
        setCities([]);
        setStateError("");
        setCityError("");
        setLoadingStates(true);

        try {
            const response = await fetch(`/api/states/${country}`);
            const result = await response.json();
            if (Array.isArray(result) && result.length > 0) {
                setStates(result);
                // Set default to state name instead of state_code
                const defaultState = result[0].name;
                setData("state", defaultState);
            } else {
                setStates([]);
                setStateError("No states found");
            }
        } catch (error) {
            console.error("Error fetching states:", error);
            setStateError("Error loading states");
        } finally {
            setLoadingStates(false);
        }
    };

    const fetchCities = async (stateCode: string) => {
        if (!stateCode) return;

        setLoadingCities(true);
        setCities([]);
        setCityError("");

        try {
            const response = await fetch(`/api/cities/${data.country}/${stateCode}`);
            const result = await response.json();
            if (Array.isArray(result) && result.length > 0) {
                setCities(result);
                setData("city", result[0].name);
            } else {
                setCities([]);
                setCityError("No cities found");
            }
        } catch (error) {
            console.error("Error fetching cities:", error);
            setCityError("Error loading cities");
        } finally {
            setLoadingCities(false);
        }
    };

    const handleStateChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
        const stateName = e.target.value;
        setData("state", stateName);
    };

    useEffect(() => {
        // Find the state code based on selected state name
        if (data.state) {
            const selectedState = states.find(s => s.name === data.state);
            if (selectedState) {
                fetchCities(selectedState.state_code);
            }
        }
    }, [data.state]);

    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);
        }
        if (flash?.error) {
            toast.error(flash.error);
        }
    }, [flash]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
       post("/company/store");
    };

    useEffect(() => {
        const handleKeyDown = (event: KeyboardEvent) => {
            if (event.key === "Enter") {
                event.preventDefault(); // Prevent form submission on Enter key
            }
        };

        document.querySelector("form")?.addEventListener("keydown", handleKeyDown);

        // Cleanup on unmount
        return () => {
            document.querySelector("form")?.removeEventListener("keydown", handleKeyDown);
        };
    }, []);


    return (
        <div className="min-h-screen bg-gray-50">
                <ToastContainer
                    position="top-right"
                    autoClose={5000}
                    hideProgressBar={false}
                    newestOnTop={false}
                    closeOnClick
                    rtl={false}
                    pauseOnFocusLoss
                    draggable
                    pauseOnHover
                />

            {/* Main Form Container */}
            <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div className="bg-white rounded-2xl shadow-xl overflow-hidden transition-all duration-300 hover:shadow-2xl">
                    <div className="px-8 py-6 bg-indigo-50 border-b border-indigo-100">
                        <h1 className="text-5xl font-bold text-indigo-600 tracking-tight mb-10 underline">
                            FlowPOS
                        </h1>
                        <h1 className="text-2xl font-bold text-indigo-600">
                            Create Company
                        </h1>
                        <p className="mt-1 text-indigo-500">
                            Register your organization
                        </p>

                        {/* Back to Login Link */}
                        <Link
                            href={route("logout")}
                            method="post" 
                            as="button"
                            className="flex items-center text-indigo-500 hover:text-indigo-600 mt-4"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                className="w-5 h-5 mr-2"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 12H5m7 7l-7-7 7-7"
                                />
                            </svg>
                            <span>Back to Login</span>
                        </Link>
                    </div>

                    <form
                        onSubmit={handleSubmit}
                        className="px-8 py-6 space-y-6"
                    >
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {/* Company Name */}
                            <div className="col-span-2">
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Company Name
                                </label>
                                <input
                                    type="text"
                                    value={data.name}
                                    placeholder="name"
                                    onChange={(e) =>
                                        setData("name", e.target.value)
                                    }
                                    required
                                    className="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition duration-200"
                                />
                            </div>

                            {/* Company Email */}
                            <div className="col-span-2">
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Email
                                </label>
                                <input
                                    type="text"
                                    value={data.email}
                                    placeholder="yourmail@mail.com"
                                    onChange={(e) =>
                                        setData("email", e.target.value)
                                    }
                                    required
                                    className="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition duration-200"
                                />
                            </div>

                            {/* Phone Number */}
                            <div className="col-span-2">
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Phone Number
                                </label>
                                <input
                                    value={data.phone_number}
                                    onChange={(e) => setData("phone_number", e.target.value)}
                                    required
                                    placeholder="+123456789"
                                    className="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition duration-200"
                                />
                            </div>

                            {/* Street Address */}
                            <div className="col-span-2">
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Street Address
                                </label>
                                <input
                                    type="text"
                                    value={data.street_address}
                                    onChange={(e) =>
                                        setData(
                                            "street_address",
                                            e.target.value
                                        )
                                    }
                                    required
                                    className="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition duration-200"
                                />
                            </div>

                            {/* Country Select */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Country
                                </label>
                                <select
                                    value={data.country}
                                    onChange={handleCountryChange}
                                    required
                                    className="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition duration-200"
                                >
                                    <option value="">Select a country</option>
                                    {countries.map((country) => (
                                        <option
                                            key={country.iso_code_2}
                                            value={country.iso_code_2}
                                        >
                                            {country.name}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            {/* State Select */}
                            <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                    State
                </label>
                <select
                    value={data.state}
                    onChange={handleStateChange}
                    required
                    disabled={loadingStates || states.length === 0}
                    className="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition duration-200 disabled:opacity-50"
                >
                    {loadingStates ? (
                        <option>Loading states...</option>
                    ) : states.length > 0 ? (
                        states.map((state) => (
                            <option
                                key={state.state_code}
                                value={state.name} // Store state name instead of code
                            >
                                {state.name}
                            </option>
                        ))
                    ) : (
                        <option>{stateError || "Select a country first"}</option>
                    )}
                </select>
            </div>

                            {/* City Select */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    City
                                </label>
                                <select
                                    value={data.city}
                                    onChange={(e) =>
                                        setData("city", e.target.value)
                                    }
                                    required
                                    disabled={
                                        loadingCities || cities.length === 0
                                    }
                                    className="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition duration-200 disabled:opacity-50"
                                >
                                    {loadingCities ? (
                                        <option>Loading cities...</option>
                                    ) : cities.length > 0 ? (
                                        cities.map((city) => (
                                            <option
                                                key={city.name}
                                                value={city.name}
                                            >
                                                {city.name}
                                            </option>
                                        ))
                                    ) : (
                                        <option>
                                            {cityError ||
                                                "Select a state first"}
                                        </option>
                                    )}
                                </select>
                            </div>

                            {/* Postal Code */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Postal Code
                                </label>
                                <input
                                    type="text"
                                    value={data.postal_code}
                                    onChange={(e) =>
                                        setData("postal_code", e.target.value)
                                    }
                                    className="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition duration-200"
                                />
                            </div>
                        </div>

                        {/* Submit Button */}
                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full py-3 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:scale-[1.01] shadow-md hover:shadow-lg"
                        >
                             {processing ? "Creating Company..." : "Create Company"}
                        </button>
                    </form>
                    <p className="text-center pb-2 text-sm text-gray-600 hover:cursor-default">Click on button to create</p>
                </div>
            </div>
        </div>
    );
}


export default CreateCompany;
